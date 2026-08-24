import './pocketbase-hooks.d.ts';

routerAdd("POST", "/api/livekit/token", (e) => {
  const info = $apis.requestInfo(e);
  if (!info.authRecord) {
    return e.json(401, { message: "Unauthorized" });
  }

  const channelId = info.data.channelId;
  const serverId = info.data.serverId;

  if (!channelId || !serverId) {
    return e.json(400, { message: "channelId and serverId are required" });
  }

  // Verify membership
  const userId = info.authRecord.id;
  const members = $app.findRecordsByFilter("members", `server = "${serverId}" && user = "${userId}"`);
  if (members.length === 0) {
    return e.json(403, { message: "Access denied to server channel" });
  }

  const apiKey = process.env.LIVEKIT_API_KEY || "devkey";
  const apiSecret = process.env.LIVEKIT_API_SECRET || "secretsecretsecretsecretsecretsecret";
  const roomName = `room-${serverId}-${channelId}`;
  const identity = userId;
  const username = info.authRecord.getString("name") || info.authRecord.getString("email") || userId;

  // Simple LiveKit JWT claims
  const now = Math.floor(Date.now() / 1000);
  const exp = now + 24 * 60 * 60; // 24 hours

  const header = { alg: "HS256", typ: "JWT" };
  const payload = {
    iss: apiKey,
    sub: identity,
    nbf: now,
    exp: exp,
    name: username,
    video: {
      room: roomName,
      roomJoin: true,
      canPublish: true,
      canSubscribe: true,
      canPublishData: true,
    }
  };

  const base64Url = (str: string) => {
    return $security.base64UrlEncode(str);
  };

  const headerEncoded = base64Url(JSON.stringify(header));
  const payloadEncoded = base64Url(JSON.stringify(payload));
  const signatureInput = `${headerEncoded}.${payloadEncoded}`;
  const signature = $security.hs256(signatureInput, apiSecret);
  const token = `${signatureInput}.${signature}`;

  return e.json(200, {
    token,
    url: process.env.VITE_LIVEKIT_URL || "ws://localhost:7880",
    roomName,
  });
});
