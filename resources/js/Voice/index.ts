import * as mqtt from 'mqtt';
import {joinRoom, Room, selfId} from 'trystero/mqtt';
import echo from '@/echo';
import {PresenceChannel} from 'laravel-echo';
import {User} from '@/types';

/**
 * A fake MQTT client that routes messages through Laravel Echo (Reverb) whispers.
 */
class ReverbMqttClient {
    public options = {host: 'reverb', path: '/voice'};
    public stream = {socket: {readyState: 1}};
    private handlers: Record<string, ((...args: unknown[]) => void)[]> = {};

    constructor(private echoChannel: PresenceChannel) {
        this.echoChannel.listenForWhisper('trystero-mqtt', (data: { topic: string, message: string }) => {
            // Trystero expects the message to be a Buffer-like object with a toString method
            this.emit('message', data.topic, {
                toString: () => data.message
            });
        });
    }

    on(event: string, cb: (...args: unknown[]) => void) {
        this.handlers[event] = this.handlers[event] || [];
        this.handlers[event].push(cb);
        if (event === 'connect') {
            // Simulate immediate connection
            setTimeout(() => this.emit('connect'), 0);
        }
        return this;
    }

    subscribe(_topic: string) {
        // Echo handles "subscription" via the presence channel, so we don't need to do anything here.
        return this;
    }

    unsubscribe(_topic: string) {
        return this;
    }

    publish(topic: string, message: string | { toString: () => string }) {
        this.echoChannel.whisper('trystero-mqtt', {
            topic,
            message: message.toString()
        });
        return this;
    }

    // Mock other expected mqtt client methods if necessary
    end() {
        return this;
    }

    private emit(event: string, ...args: unknown[]) {
        this.handlers[event]?.forEach(cb => cb(...args));
    }
}

export interface VoiceChannel {
    room: Room;
    leave: () => void;
    users: (cb: (users: User[]) => void) => void;
}

/**
 * Joins a voice channel using Reverb for signaling and Trystero for WebRTC.
 *
 * @param channelId The ID of the voice channel to join.
 * @returns An object containing the Trystero room and helper methods.
 */
export const joinVoiceChannel = (channelId: number): VoiceChannel => {
    const echoChannel = echo.join(`voices.${channelId}`);

    // Monkey-patch mqtt.connect to return our Reverb-backed client when it's called with 'reverb' URL.
    const mqttModule = mqtt as {
        connect: (url: string | object, options?: unknown) => unknown;
        default?: { connect: (url: string | object, options?: unknown) => unknown };
        _originalConnect?: (url: string | object, options?: unknown) => unknown;
    };

    const originalConnect = mqttModule.connect || mqttModule.default?.connect;

    if (!mqttModule._originalConnect) {
        mqttModule._originalConnect = originalConnect;
    }

    mqttModule.connect = (url: string | object, options?: unknown) => {
        if (url === 'reverb' || (typeof url === 'object' && (url as { host?: string }).host === 'reverb')) {
            return new ReverbMqttClient(echoChannel);
        }
        return mqttModule._originalConnect!(url, options);
    };

    // joinRoom will call mqtt.connect('reverb')
    const room = joinRoom({
        relayUrls: ['reverb'],
        appId: 'oxy-voice'
    }, `channel-${channelId}`);

    return {
        room,
        leave: () => {
            room.leave();
            echo.leave(`voices.${channelId}`);
        },
        users: (cb: (users: User[]) => void) => {
            echoChannel.here(cb);
            echoChannel.joining(() => echoChannel.here(cb));
            echoChannel.leaving(() => echoChannel.here(cb));
        }
    };
};

export {selfId};
