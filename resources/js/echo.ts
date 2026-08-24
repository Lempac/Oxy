// Echo compatibility stub for PocketBase SSE migration
const dummyChannel = {
    listen() { return dummyChannel; },
    listenForWhisper() { return dummyChannel; },
    stopListening() { return dummyChannel; },
    whisper() { return dummyChannel; },
    here(cb: any) { if (typeof cb === 'function') cb([]); return dummyChannel; },
    joining(cb: any) { return dummyChannel; },
    leaving(cb: any) { return dummyChannel; }
};

export const echo = {
    private() { return dummyChannel; },
    channel() { return dummyChannel; },
    join() { return dummyChannel; },
    leave() { return dummyChannel; }
};

export default echo;
