migrate((app) => {
  const channels = app.findCollectionByNameOrId("channels");
  const collection = new Collection({
    name: "whiteboards",
    type: "base",
    listRule: "@request.auth.id != ''",
    viewRule: "@request.auth.id != ''",
    createRule: "@request.auth.id != ''",
    updateRule: "@request.auth.id != ''",
    deleteRule: "@request.auth.id != ''",
    fields: [
      { name: "channel", type: "relation", collectionId: channels.id, required: true, cascadeDelete: true },
      { name: "state", type: "json" },
      { name: "sync_status", type: "text" },
      { name: "created", type: "autodate", onCreate: true },
      { name: "updated", type: "autodate", onCreate: true, onUpdate: true }
    ]
  });
  return app.save(collection);
}, (app) => {
  const collection = app.findCollectionByNameOrId("whiteboards");
  if (collection) return app.delete(collection);
});
