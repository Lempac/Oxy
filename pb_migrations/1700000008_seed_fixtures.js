migrate((app) => {
  const usersCol = app.findCollectionByNameOrId("_pb_users_auth_");
  const serversCol = app.findCollectionByNameOrId("servers");
  const rolesCol = app.findCollectionByNameOrId("roles");
  const channelsCol = app.findCollectionByNameOrId("channels");
  const membersCol = app.findCollectionByNameOrId("members");
  const messagesCol = app.findCollectionByNameOrId("messages");
  const invitesCol = app.findCollectionByNameOrId("invites");

  // 1. Seed Core Test Users (testuser, moderator, member)
  let testUser;
  try {
    testUser = app.findFirstRecordByData("_pb_users_auth_", "name", "testuser");
  } catch {
    testUser = new Record(usersCol, {
      name: "testuser",
      status: "online"
    });
    testUser.setPassword("password123");
    app.save(testUser);
  }

  let modUser;
  try {
    modUser = app.findFirstRecordByData("_pb_users_auth_", "name", "moderator");
  } catch {
    modUser = new Record(usersCol, {
      name: "moderator",
      status: "online"
    });
    modUser.setPassword("password123");
    app.save(modUser);
  }

  let memberUser;
  try {
    memberUser = app.findFirstRecordByData("_pb_users_auth_", "name", "member");
  } catch {
    memberUser = new Record(usersCol, {
      name: "member",
      status: "idle"
    });
    memberUser.setPassword("password123");
    app.save(memberUser);
  }

  // 2. Seed Server: Oxy HQ
  let oxyHq;
  try {
    oxyHq = app.findFirstRecordByData("servers", "slug", "oxy-hq");
  } catch {
    oxyHq = new Record(serversCol, {
      name: "Oxy HQ",
      slug: "oxy-hq",
      description: "Official Oxy development & preview headquarters.",
      owner: testUser.id,
      enable_whiteboard: true
    });
    app.save(oxyHq);
  }

  // 3. Seed Roles for Oxy HQ
  let adminRole, modRole, memberRole;
  try {
    adminRole = app.findFirstRecordByFilter("roles", `server = "${oxyHq.id}" && name = "Admin"`);
  } catch {
    adminRole = new Record(rolesCol, {
      server: oxyHq.id,
      name: "Admin",
      color: "#ef4444",
      importance: 1,
      perms: ["ADMINISTRATOR"]
    });
    app.save(adminRole);
  }

  try {
    modRole = app.findFirstRecordByFilter("roles", `server = "${oxyHq.id}" && name = "Moderator"`);
  } catch {
    modRole = new Record(rolesCol, {
      server: oxyHq.id,
      name: "Moderator",
      color: "#3b82f6",
      importance: 2,
      perms: ["KICK_MEMBERS", "BAN_MEMBERS"]
    });
    app.save(modRole);
  }

  try {
    memberRole = app.findFirstRecordByFilter("roles", `server = "${oxyHq.id}" && name = "Member"`);
  } catch {
    memberRole = new Record(rolesCol, {
      server: oxyHq.id,
      name: "Member",
      color: "#10b981",
      importance: 3,
      perms: ["SEND_MESSAGES", "ATTACH_FILES", "CONNECT_VOICE", "SPEAK_VOICE"]
    });
    app.save(memberRole);
  }

  // Members for Oxy HQ
  const memberRoleMap = [
    { uid: testUser.id, roleId: adminRole.id },
    { uid: modUser.id, roleId: modRole.id },
    { uid: memberUser.id, roleId: memberRole.id }
  ];
  for (const m of memberRoleMap) {
    try {
      app.findFirstRecordByFilter("members", `server = "${oxyHq.id}" && user = "${m.uid}"`);
    } catch {
      const memberRec = new Record(membersCol, {
        server: oxyHq.id,
        user: m.uid,
        role: m.roleId
      });
      app.save(memberRec);
    }
  }

  // Channels for Oxy HQ
  const channelDefs = [
    { name: "announcements", slug: "announcements", type: "text", position: 0 },
    { name: "general", slug: "general", type: "text", position: 1 },
    { name: "random", slug: "random", type: "text", position: 2 },
    { name: "Community Lounge", slug: "community-lounge", type: "voice", position: 3 },
    { name: "Project Canvas", slug: "project-canvas", type: "whiteboard", position: 4 }
  ];

  for (const def of channelDefs) {
    let chan;
    try {
      chan = app.findFirstRecordByData("channels", "slug", def.slug);
    } catch {
      chan = new Record(channelsCol, {
        server: oxyHq.id,
        name: def.name,
        slug: def.slug,
        type: def.type,
        position: def.position
      });
      app.save(chan);

      if (def.slug === "announcements") {
        const msg = new Record(messagesCol, {
          channel: chan.id,
          user: testUser.id,
          content: "🎉 Welcome to Oxy! Powered by PocketBase, LiveKit WebRTC, and Yjs whiteboards.",
          status: "sent"
        });
        app.save(msg);
      } else if (def.slug === "general") {
        const m1 = new Record(messagesCol, {
          channel: chan.id,
          user: testUser.id,
          content: "Hey everyone, welcome to the Oxy HQ general channel!",
          status: "sent"
        });
        app.save(m1);
        const m2 = new Record(messagesCol, {
          channel: chan.id,
          user: modUser.id,
          content: "Real-time SSE events, LiveKit voice/video, and channel switching are working smoothly.",
          status: "sent"
        });
        app.save(m2);
      }
    }
  }

  // Invite for Oxy HQ
  try {
    app.findFirstRecordByData("invites", "code", "OXY-PREVIEW");
  } catch {
    const invite = new Record(invitesCol, {
      server: oxyHq.id,
      created_by_user: testUser.id,
      code: "OXY-PREVIEW",
      uses: 0
    });
    app.save(invite);
  }
}, (app) => {
  // Rollback
});
