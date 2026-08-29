migrate((app) => {
  const usersCol = app.findCollectionByNameOrId("_pb_users_auth_");
  const serversCol = app.findCollectionByNameOrId("servers");
  const rolesCol = app.findCollectionByNameOrId("roles");
  const membersCol = app.findCollectionByNameOrId("members");

  let testUser, modUser, memberUser, oxyHq;
  try {
    testUser = app.findFirstRecordByData("_pb_users_auth_", "name", "testuser");
    modUser = app.findFirstRecordByData("_pb_users_auth_", "name", "moderator");
    memberUser = app.findFirstRecordByData("_pb_users_auth_", "name", "member");
    oxyHq = app.findFirstRecordByData("servers", "slug", "oxy-hq");
  } catch {
    return; // Skip if initial seed hasn't run yet
  }

  if (!oxyHq) return;

  // 1. Ensure Roles
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

  // 2. Ensure Members & Roles
  const memberMap = [
    { user: testUser, role: adminRole },
    { user: modUser, role: modRole },
    { user: memberUser, role: memberRole }
  ];

  for (const m of memberMap) {
    if (!m.user) continue;
    let memberRec;
    try {
      memberRec = app.findFirstRecordByFilter("members", `server = "${oxyHq.id}" && user = "${m.user.id}"`);
      if (!memberRec.get("role")) {
        memberRec.set("role", m.role.id);
        app.save(memberRec);
      }
    } catch {
      memberRec = new Record(membersCol, {
        server: oxyHq.id,
        user: m.user.id,
        role: m.role.id
      });
      app.save(memberRec);
    }
  }
}, (app) => {
  // Rollback
});
