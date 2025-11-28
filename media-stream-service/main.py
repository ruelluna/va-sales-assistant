#!/usr/bin/env python3
"""
Twilio Media Stream to Deepgram Bridge Service

This service accepts Twilio Media Stream WebSocket connections,
forwards audio to Deepgram for real-time transcription,
and sends transcript chunks to Laravel via HTTP POST.
"""

import asyncio
import json
import os
import base64
import websockets
import aiohttp
from deepgram import DeepgramClient, PrerecordedOptions, LiveOptions, LiveTranscriptionEvents
from deepgram.clients.live import LiveClient
from dotenv import load_dotenv

load_dotenv()

DEEPGRAM_API_KEY = os.getenv('DEEPGRAM_API_KEY')
LARAVEL_API_URL = os.getenv('LARAVEL_API_URL', 'http://laravel:8000')
LARAVEL_API_TOKEN = os.getenv('LARAVEL_API_TOKEN')
MEDIA_STREAM_PORT = int(os.getenv('MEDIA_STREAM_PORT', 8080))


class MediaStreamHandler:
    def __init__(self):
        self.deepgram = DeepgramClient(DEEPGRAM_API_KEY)
        self.active_streams = {}

    async def handle_twilio_stream(self, websocket, path):
        """Handle incoming Twilio Media Stream WebSocket connection"""
        call_session_id = None
        twilio_call_sid = None
        deepgram_connection = None

        try:
            # Receive initial connection parameters from Twilio
            initial_message = await websocket.recv()
            if isinstance(initial_message, str):
                params = json.loads(initial_message)
                call_session_id = params.get('callSessionId')
                twilio_call_sid = params.get('twilioCallSid')

            # Create Deepgram live connection
            deepgram_connection = self.deepgram.listen.websocket.v("1")

            # Set up Deepgram event handlers
            deepgram_connection.on(LiveTranscriptionEvents.Open, self.on_deepgram_open)
            deepgram_connection.on(LiveTranscriptionEvents.Transcript, lambda *args: self.on_deepgram_transcript(*args, call_session_id=call_session_id))
            deepgram_connection.on(LiveTranscriptionEvents.Error, self.on_deepgram_error)
            deepgram_connection.on(LiveTranscriptionEvents.Close, self.on_deepgram_close)

            # Start Deepgram connection
            if not deepgram_connection.start(LiveOptions(
                model="nova-2",
                language="en-US",
                smart_format=True,
                interim_results=True,
                utterance_end_ms=1000,
                vad_events=True,
            )):
                print("Failed to start Deepgram connection")
                return

            # Forward audio from Twilio to Deepgram
            async for message in websocket:
                if isinstance(message, str):
                    data = json.loads(message)
                    event = data.get('event')

                    if event == 'media':
                        # Decode mu-law audio from Twilio
                        payload = data.get('media', {}).get('payload')
                        if payload:
                            # Convert base64 mu-law to PCM
                            audio_data = base64.b64decode(payload)
                            # Deepgram expects PCM16 audio
                            pcm_audio = self.mulaw_to_pcm(audio_data)
                            # Send to Deepgram
                            deepgram_connection.send(pcm_audio)

        except websockets.exceptions.ConnectionClosed:
            print(f"Twilio connection closed for call {call_session_id}")
        except Exception as e:
            print(f"Error handling stream: {e}")
        finally:
            if deepgram_connection:
                deepgram_connection.finish()
            if call_session_id:
                self.active_streams.pop(call_session_id, None)

    def mulaw_to_pcm(self, mulaw_data):
        """Convert mu-law audio to PCM16"""
        # Mu-law to linear conversion
        mulaw_table = [
            -32124, -31100, -30076, -29052, -28028, -27004, -25980, -24956,
            -23932, -22908, -21884, -20860, -19836, -18812, -17788, -16764,
            -15996, -15484, -14972, -14460, -13948, -13436, -12924, -12412,
            -11900, -11388, -10876, -10364, -9852, -9340, -8828, -8316,
            -7932, -7676, -7420, -7164, -6908, -6652, -6396, -6140,
            -5884, -5628, -5372, -5116, -4860, -4604, -4348, -4092,
            -3900, -3772, -3644, -3516, -3388, -3260, -3132, -3004,
            -2876, -2748, -2620, -2492, -2364, -2236, -2108, -1980,
            -1884, -1820, -1756, -1692, -1628, -1564, -1500, -1436,
            -1372, -1308, -1244, -1180, -1116, -1052, -988, -924,
            -876, -844, -812, -780, -748, -716, -684, -652,
            -620, -588, -556, -524, -492, -460, -428, -396,
            -372, -356, -340, -324, -308, -292, -276, -260,
            -244, -228, -212, -196, -180, -164, -148, -132,
            -120, -112, -104, -96, -88, -80, -72, -64,
            -56, -48, -40, -32, -24, -16, -8, 0,
            32124, 31100, 30076, 29052, 28028, 27004, 25980, 24956,
            23932, 22908, 21884, 20860, 19836, 18812, 17788, 16764,
            15996, 15484, 14972, 14460, 13948, 13436, 12924, 12412,
            11900, 11388, 10876, 10364, 9852, 9340, 8828, 8316,
            7932, 7676, 7420, 7164, 6908, 6652, 6396, 6140,
            5884, 5628, 5372, 5116, 4860, 4604, 4348, 4092,
            3900, 3772, 3644, 3516, 3388, 3260, 3132, 3004,
            2876, 2748, 2620, 2492, 2364, 2236, 2108, 1980,
            1884, 1820, 1756, 1692, 1628, 1564, 1500, 1436,
            1372, 1308, 1244, 1180, 1116, 1052, 988, 924,
            876, 844, 812, 780, 748, 716, 684, 652,
            620, 588, 556, 524, 492, 460, 428, 396,
            372, 356, 340, 324, 308, 292, 276, 260,
            244, 228, 212, 196, 180, 164, 148, 132,
            120, 112, 104, 96, 88, 80, 72, 64,
            56, 48, 40, 32, 24, 16, 8, 0
        ]

        pcm_data = bytearray()
        for byte in mulaw_data:
            pcm_value = mulaw_table[byte]
            # Convert to 16-bit signed integer (little-endian)
            pcm_data.extend(pcm_value.to_bytes(2, byteorder='little', signed=True))

        return bytes(pcm_data)

    def on_deepgram_open(self, *args, **kwargs):
        """Handle Deepgram connection open"""
        print("Deepgram connection opened")

    async def on_deepgram_transcript(self, *args, call_session_id=None, **kwargs):
        """Handle Deepgram transcript events"""
        if not args or len(args) == 0:
            return

        result = args[0]
        if not result:
            return

        sentence = result.channel.alternatives[0].transcript if result.channel.alternatives else None
        if not sentence or not sentence.strip():
            return

        # Determine speaker (Deepgram can provide speaker diarization)
        # For now, we'll use a simple heuristic or default to 'prospect'
        speaker = 'prospect'  # Default, can be enhanced with Deepgram speaker diarization
        if hasattr(result, 'channel') and hasattr(result.channel, 'alternatives'):
            # Check if Deepgram provides speaker information
            pass

        # Calculate timestamp (relative to call start)
        timestamp = result.start if hasattr(result, 'start') else 0.0

        # Send to Laravel
        await self.send_to_laravel(call_session_id, speaker, sentence, timestamp)

    async def send_to_laravel(self, call_session_id, speaker, text, timestamp):
        """Send transcript chunk to Laravel API"""
        if not call_session_id:
            return

        url = f"{LARAVEL_API_URL}/api/media-stream"
        payload = {
            'call_session_id': call_session_id,
            'speaker': speaker,
            'text': text,
            'timestamp': timestamp,
        }

        headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }

        if LARAVEL_API_TOKEN:
            headers['Authorization'] = f'Bearer {LARAVEL_API_TOKEN}'

        try:
            async with aiohttp.ClientSession() as session:
                async with session.post(url, json=payload, headers=headers) as response:
                    if response.status == 201:
                        print(f"Sent transcript to Laravel: {text[:50]}...")
                    else:
                        print(f"Failed to send transcript: {response.status}")
        except Exception as e:
            print(f"Error sending to Laravel: {e}")

    def on_deepgram_error(self, *args, **kwargs):
        """Handle Deepgram errors"""
        if args:
            print(f"Deepgram error: {args[0]}")

    def on_deepgram_close(self, *args, **kwargs):
        """Handle Deepgram connection close"""
        print("Deepgram connection closed")


async def main():
    """Main entry point"""
    handler = MediaStreamHandler()

    print(f"Starting Media Stream Service on port {MEDIA_STREAM_PORT}...")
    async with websockets.serve(handler.handle_twilio_stream, "0.0.0.0", MEDIA_STREAM_PORT):
        await asyncio.Future()  # Run forever


if __name__ == "__main__":
    asyncio.run(main())
