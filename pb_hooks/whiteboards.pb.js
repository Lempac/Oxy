"use strict";
var import_pocketbase_hooks_d = require("./pocketbase-hooks.d.ts");
routerAdd("POST", "/api/whiteboard/{id}/save", (e) => {
  const info = $apis.requestInfo(e);
  if (!info.authRecord) {
    return e.json(401, { message: "Unauthorized" });
  }
  const id = e.request.pathValue("id");
  const state = info.data.state;
  if (!id) {
    return e.json(400, { message: "Whiteboard ID is required" });
  }
  try {
    let record;
    try {
      record = $app.findFirstRecordByData("whiteboards", "channel", id);
    } catch {
      const collection = $app.findCollectionByNameOrId("whiteboards");
      record = new Record(collection, {
        channel: id
      });
    }
    record.set("state", state);
    record.set("sync_status", "synced");
    $app.save(record);
    return e.json(200, { success: true });
  } catch {
    return e.json(500, { message: "Failed to save whiteboard state" });
  }
});
