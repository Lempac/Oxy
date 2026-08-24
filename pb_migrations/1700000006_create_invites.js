migrate((app) => {
  const servers = app.findCollectionByNameOrId("servers");
  const collection = new Collection({
    name: "invites",
    type: "base",
    listRule: "@request.auth.id != ''",
    viewRule: "@request.auth.id != ''",
    createRule: "@request.auth.id != ''",
    updateRule: "@request.auth.id != ''",
    deleteRule: "@request.auth.id != ''",
    fields: [
      { name: "server", type: "relation", collectionId: servers.id, required: true, cascadeDelete: true },
      { name: "created_by_user", type: "relation", collectionId: "_pb_users_auth_" },
      { name: "code", type: "text", required: true, unique: true },
      { name: "max_uses", type: "number" },
      { name: "uses", type: "number" },
      { name: "expires_at", type: "date" }
    ]
  });
  return app.save(collection);
}, (app) => {
  const collection = app.findCollectionByNameOrId("invites");
  if (collection) return app.delete(collection);
});
