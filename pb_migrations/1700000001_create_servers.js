migrate((app) => {
  const collection = new Collection({
    name: "servers",
    type: "base",
    listRule: "@request.auth.id != ''",
    viewRule: "@request.auth.id != ''",
    createRule: "@request.auth.id != ''",
    updateRule: "owner = @request.auth.id",
    deleteRule: "owner = @request.auth.id",
    fields: [
      { name: "name", type: "text", required: true },
      { name: "slug", type: "text", required: true, unique: true },
      { name: "description", type: "text" },
      { name: "icon", type: "file", maxSelect: 1, mimeTypes: ["image/png", "image/jpeg", "image/gif", "image/webp", "image/svg+xml"] },
      { name: "owner", type: "relation", collectionId: "_pb_users_auth_", required: true, cascadeDelete: true },
      { name: "enable_whiteboard", type: "bool" }
    ]
  });
  return app.save(collection);
}, (app) => {
  const collection = app.findCollectionByNameOrId("servers");
  if (collection) return app.delete(collection);
});
