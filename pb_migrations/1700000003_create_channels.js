migrate((app) => {
  const servers = app.findCollectionByNameOrId("servers");
  const collection = new Collection({
    name: "channels",
    type: "base",
    listRule: "@request.auth.id != ''",
    viewRule: "@request.auth.id != ''",
    createRule: "@request.auth.id != ''",
    updateRule: "@request.auth.id != ''",
    deleteRule: "@request.auth.id != ''",
    fields: [
      { name: "server", type: "relation", collectionId: servers.id, required: true, cascadeDelete: true },
      { name: "name", type: "text", required: true },
      { name: "slug", type: "text", required: true },
      { name: "type", type: "select", select: { maxSelect: 1, values: ["text", "voice", "whiteboard"] }, required: true },
      { name: "position", type: "number" }
    ]
  });
  return app.save(collection);
}, (app) => {
  const collection = app.findCollectionByNameOrId("channels");
  if (collection) return app.delete(collection);
});
