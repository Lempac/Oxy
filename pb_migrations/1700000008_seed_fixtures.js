migrate((app) => {
  const usersCol = app.findCollectionByNameOrId("_pb_users_auth_");
  const serversCol = app.findCollectionByNameOrId("servers");
  const channelsCol = app.findCollectionByNameOrId("channels");
  const membersCol = app.findCollectionByNameOrId("members");
  const messagesCol = app.findCollectionByNameOrId("messages");
  const invitesCol = app.findCollectionByNameOrId("invites");

  // 1. Seed Core Test Users (testuser, moderator, member)
  let testUser;
  try {
    testUser = app.findFirstRecordByData("_pb_users_auth_", "username", "testuser");
  } catch {
    testUser = new Record(usersCol, {
      username: "testuser",
      email: "testuser@oxy.local",
      name: "testuser",
      status: "online"
    });
    testUser.setPassword("password123");
    app.save(testUser);
  }

  let modUser;
  try {
    modUser = app.findFirstRecordByData("_pb_users_auth_", "username", "moderator");
  } catch {
    modUser = new Record(usersCol, {
      username: "moderator",
      email: "moderator@oxy.local",
      name: "moderator",
      status: "online"
    });
    modUser.setPassword("password123");
    app.save(modUser);
  }

  let memberUser;
  try {
    memberUser = app.findFirstRecordByData("_pb_users_auth_", "username", "member");
  } catch {
    memberUser = new Record(usersCol, {
      username: "member",
      email: "member@oxy.local",
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

  // Members for Oxy HQ
  const userIds = [testUser.id, modUser.id, memberUser.id];
  for (const uid of userIds) {
    try {
      app.findFirstRecordByData("members", "server", oxyHq.id);
    } catch {
      const memberRec = new Record(membersCol, {
        server: oxyHq.id,
        user: uid
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
