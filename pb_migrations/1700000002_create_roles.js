migrate((app) => {
  const servers = app.findCollectionByNameOrId("servers");
  const collection = new Collection({
    name: "roles",
    type: "base",
    listRule: "@request.auth.id != ''",
    viewRule: "@request.auth.id != ''",
    createRule: "@request.auth.id != ''",
    updateRule: "@request.auth.id != ''",
    deleteRule: "@request.auth.id != ''",
    fields: [
      { name: "server", type: "relation", collectionId: servers.id, required: true, cascadeDelete: true },
      { name: "name", type: "text", required: true },
      { name: "color", type: "text" },
      { name: "importance", type: "number" },
      { name: "perms", type: "json" },
      { name: "permissions", type: "json" },
      { name: "created", type: "autodate", onCreate: true },
      { name: "updated", type: "autodate", onCreate: true, onUpdate: true }
    ]
  });
  return app.save(collection);
}, (app) => {
  const collection = app.findCollectionByNameOrId("roles");
  if (collection) return app.delete(collection);
});
