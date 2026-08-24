"use strict";
routerAdd("POST", "/api/server/{id}/leave", (e) => {
  const info = $apis.requestInfo(e);
  if (!info.authRecord) {
    return e.json(401, { message: "Unauthorized" });
  }
  const serverId = e.request.pathValue("id");
  const userId = info.authRecord.id;
  try {
    const members = $app.findRecordsByFilter("members", `server = "${serverId}" && user = "${userId}"`);
    for (const m of members) {
      $app.delete(m);
    }
    return e.json(200, { success: true });
  } catch (err) {
    return e.json(500, { message: "Failed to leave server" });
  }
});
