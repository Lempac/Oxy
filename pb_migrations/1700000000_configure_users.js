migrate((app) => {
  const usersCol = app.findCollectionByNameOrId("_pb_users_auth_");
  if (usersCol) {
    const emailField = usersCol.fields.getByName("email");
    if (emailField) {
      emailField.required = false;
      emailField.hidden = true;
    }
    const usernameField = usersCol.fields.getByName("username");
    if (usernameField) {
      usernameField.required = true;
    }
    // Remove unique constraint / index on email
    usersCol.indexes = (usersCol.indexes || []).filter(
      (idx) => !idx.toLowerCase().includes("email")
    );
    app.save(usersCol);
  }
}, (app) => {
  // Rollback
});
