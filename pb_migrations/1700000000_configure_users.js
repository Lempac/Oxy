migrate((app) => {
  const usersCol = app.findCollectionByNameOrId("_pb_users_auth_");
  if (usersCol) {
    const emailField = usersCol.fields.getByName("email");
    if (emailField) {
      emailField.required = false;
      emailField.hidden = true;
    }
    const nameField = usersCol.fields.getByName("name");
    if (nameField) {
      nameField.required = true;
    }
    // Remove email index
    usersCol.indexes = (usersCol.indexes || []).filter(
      (idx) => !idx.toLowerCase().includes("email")
    );
    // Add unique index on name
    const hasNameIdx = usersCol.indexes.some((idx) => idx.toLowerCase().includes("`name`"));
    if (!hasNameIdx) {
      usersCol.indexes.push("CREATE UNIQUE INDEX `idx_name__pb_users_auth_` ON `users` (`name`) WHERE `name` != ''");
    }
    app.save(usersCol);
  }
}, (app) => {
  // Rollback
});
