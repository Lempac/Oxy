/// <reference path="./pocketbase-hooks.d.ts" />

routerAdd("POST", "/profile/status", (e) => {
  const info = $apis.requestInfo(e);
  if (!info.authRecord) {
    return e.json(401, { message: "Unauthorized" });
  }

  const status = info.data.status;
  if (!status) {
    return e.json(400, { message: "Status required" });
  }

  try {
    info.authRecord.set("status", status);
    $app.save(info.authRecord);
    return e.json(200, { success: true });
  } catch (err) {
    return e.json(500, { message: "Failed to update profile status" });
  }
});
