migrate((app) => {
  const channels = app.findCollectionByNameOrId("channels");
  const collection = new Collection({
    name: "messages",
    type: "base",
    listRule: "@request.auth.id != ''",
    viewRule: "@request.auth.id != ''",
    createRule: "@request.auth.id != ''",
    updateRule: "user = @request.auth.id",
    deleteRule: "user = @request.auth.id",
    fields: [
      { name: "channel", type: "relation", collectionId: channels.id, required: true, cascadeDelete: true },
      { name: "user", type: "relation", collectionId: "_pb_users_auth_", required: true, cascadeDelete: true },
      { name: "content", type: "text" },
      { name: "status", type: "text" },
      { name: "attachments", type: "file", maxSelect: 10 }
    ]
  });
  return app.save(collection);
}, (app) => {
  const collection = app.findCollectionByNameOrId("messages");
  if (collection) return app.delete(collection);
});
