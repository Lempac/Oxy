"use strict";
routerAdd("GET", "/api/invites/{code}/check", (e) => {
  const code = e.request.pathValue("code");
  if (!code) {
    return e.json(400, { valid: false, message: "Code required" });
  }
  try {
    const invite = $app.findFirstRecordByData("invites", "code", code);
    if (!invite) {
      return e.json(404, { valid: false, message: "Invalid or expired invite code." });
    }
    const expiresAt = invite.getDateTime("expires_at");
    if (expiresAt && expiresAt.time().unix() < Date.now() / 1e3) {
      return e.json(400, { valid: false, message: "Invite code expired." });
    }
    const maxUses = invite.getInt("max_uses");
    const uses = invite.getInt("uses");
    if (maxUses > 0 && uses >= maxUses) {
      return e.json(400, { valid: false, message: "Invite code limit reached." });
    }
    const serverId = invite.getString("server");
    const server = $app.findRecordById("servers", serverId);
    const members = $app.findRecordsByFilter("members", `server = "${serverId}"`);
    return e.json(200, {
      valid: true,
      server: {
        id: server.id,
        name: server.getString("name"),
        description: server.getString("description"),
        icon: server.getString("icon") ? $app.fileUrl(server, server.getString("icon")) : null,
        members_count: members.length,
        online_count: members.length
      }
    });
  } catch (err) {
    return e.json(404, { valid: false, message: "Invalid invite code." });
  }
});
routerAdd("POST", "/api/invites/join", (e) => {
  const info = $apis.requestInfo(e);
  if (!info.authRecord) {
    return e.json(401, { message: "Unauthorized" });
  }
  const code = info.data.code;
  if (!code) {
    return e.json(400, { message: "Invite code is required." });
  }
  try {
    const invite = $app.findFirstRecordByData("invites", "code", code);
    if (!invite) {
      return e.json(404, { message: "Invalid invite code." });
    }
    const serverId = invite.getString("server");
    const userId = info.authRecord.id;
    const existing = $app.findRecordsByFilter("members", `server = "${serverId}" && user = "${userId}"`);
    if (existing.length === 0) {
      const collection = $app.findCollectionByNameOrId("members");
      const record = new Record(collection, {
        server: serverId,
        user: userId
      });
      $app.save(record);
    }
    invite.set("uses", invite.getInt("uses") + 1);
    $app.save(invite);
    return e.json(200, [200, "Successfully joined server!"]);
  } catch (err) {
    return e.json(500, { message: "Failed to join server." });
  }
});
