migrate((app) => {
  const usersCol = app.findCollectionByNameOrId("_pb_users_auth_");
  if (usersCol) {
    const emailField = usersCol.fields.getByName("email");
    if (emailField) {
      emailField.required = false;
    }
    const usernameField = usersCol.fields.getByName("username");
    if (usernameField) {
      usernameField.required = true;
    }
    app.save(usersCol);
  }
}, (app) => {
  // Rollback
});
