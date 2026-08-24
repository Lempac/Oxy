migrate((app) => {
  // 1. SERVERS
  const servers = new Collection({
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
  app.save(servers);

  // 2. ROLES
  const roles = new Collection({
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
      { name: "permissions", type: "json" }
    ]
  });
  app.save(roles);

  // 3. CHANNELS
  const channels = new Collection({
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
  app.save(channels);

  // 4. MEMBERS
  const members = new Collection({
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
      { name: "role", type: "relation", collectionId: roles.id }
    ]
  });
  app.save(members);

  // 5. MESSAGES
  const messages = new Collection({
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
  app.save(messages);

  // 6. INVITES
  const invites = new Collection({
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
  app.save(invites);

  // 7. WHITEBOARDS
  const whiteboards = new Collection({
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
      { name: "sync_status", type: "text" }
    ]
  });
  app.save(whiteboards);
}, (app) => {
  const collections = ["whiteboards", "invites", "messages", "members", "channels", "roles", "servers"];
  for (const name of collections) {
    const col = app.findCollectionByNameOrId(name);
    if (col) app.delete(col);
  }
});
