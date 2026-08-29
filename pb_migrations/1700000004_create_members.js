migrate((app) => {
  const servers = app.findCollectionByNameOrId("servers");
  const roles = app.findCollectionByNameOrId("roles");
  const collection = new Collection({
    name: "members",
    type: "base",
    listRule: "@request.auth.id != ''",
    viewRule: "@request.auth.id != ''",
    createRule: "@request.auth.id != ''",
    updateRule: "@request.auth.id != ''",
    deleteRule: "@request.auth.id != ''",
    fields: [
      { name: "server", type: "relation", collectionId: servers.id, required: true, cascadeDelete: true },
      { name: "user", type: "relation", collectionId: "_pb_users_auth_", required: true, cascadeDelete: true },
      { name: "role", type: "relation", collectionId: roles.id },
      { name: "created", type: "autodate", onCreate: true },
      { name: "updated", type: "autodate", onCreate: true, onUpdate: true }
    ]
  });
  return app.save(collection);
}, (app) => {
  const collection = app.findCollectionByNameOrId("members");
  if (collection) return app.delete(collection);
});
