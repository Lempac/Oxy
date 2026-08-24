import { describe, it, expect } from 'vitest';
import {
    validateSingleFile,
    validateFilesBatch,
    validateMessageContent,
    formatFileSize,
    getFileExtension,
    isImageFile,
    isVideoFile,
    isAudioFile,
    MAX_FILE_SIZE,
} from './fileValidation';

describe('fileValidation utility', () => {
    it('correctly extracts file extensions', () => {
        expect(getFileExtension('AutoClicker.exe')).toBe('exe');
        expect(getFileExtension('image.PNG')).toBe('png');
        expect(getFileExtension('archive.tar.gz')).toBe('gz');
        expect(getFileExtension('noextension')).toBe('');
    });

    it('identifies image files by extension and mime type', () => {
        expect(isImageFile('photo.jpg')).toBe(true);
        expect(isImageFile('photo.png')).toBe(true);
        expect(isImageFile('photo.webp')).toBe(true);
        expect(isImageFile('image/jpeg')).toBe(true);
        expect(isImageFile('document.pdf')).toBe(false);
        expect(isImageFile('app.exe')).toBe(false);
    });

    it('identifies video and audio files by extension and mime type', () => {
        expect(isVideoFile('video.mp4')).toBe(true);
        expect(isVideoFile('clip.webm')).toBe(true);
        expect(isVideoFile('movie.mkv')).toBe(true);
        expect(isVideoFile('video/mp4')).toBe(true);
        expect(isVideoFile('photo.png')).toBe(false);

        expect(isAudioFile('track.mp3')).toBe(true);
        expect(isAudioFile('voice.wav')).toBe(true);
        expect(isAudioFile('sound.ogg')).toBe(true);
        expect(isAudioFile('audio/mpeg')).toBe(true);
        expect(isAudioFile('document.pdf')).toBe(false);
    });

    it('formats file sizes accurately', () => {
        expect(formatFileSize(0)).toBe('0 B');
        expect(formatFileSize(1024)).toBe('1 KB');
        expect(formatFileSize(1024 * 1024)).toBe('1 MB');
        expect(formatFileSize(25 * 1024 * 1024)).toBe('25 MB');
    });

    it('validates single files and rejects blocked extensions', () => {
        const phpFile = new File(['<?php echo "hi";'], 'script.php', { type: 'text/x-php' });
        const validation = validateSingleFile(phpFile);
        expect(validation.valid).toBe(false);
        expect(validation.error).toContain('.php');

        const exeFile = new File(['binary content'], 'AutoClicker.exe', { type: 'application/x-msdownload' });
        const exeValidation = validateSingleFile(exeFile);
        expect(exeValidation.valid).toBe(true);
    });

    it('rejects files exceeding the max file size limit', () => {
        const largeFile = new File([new Uint8Array(MAX_FILE_SIZE + 1024)], 'large.zip', { type: 'application/zip' });
        const validation = validateSingleFile(largeFile);
        expect(validation.valid).toBe(false);
        expect(validation.error).toContain('exceeds the maximum allowed file size');
    });

    it('validates batch file uploads and respects max attachment limit', () => {
        const currentFiles: File[] = [];
        const newFiles = Array.from({ length: 12 }, (_, i) => new File(['content'], `file${i}.txt`, { type: 'text/plain' }));

        const result = validateFilesBatch(currentFiles, newFiles);
        expect(result.validFiles.length).toBe(10);
        expect(result.errors.length).toBeGreaterThan(0);
    });

    it('validates message content and requires either text or attachment', () => {
        expect(validateMessageContent('', 0).valid).toBe(false);
        expect(validateMessageContent('   ', 0).valid).toBe(false);
        expect(validateMessageContent('Hello!', 0).valid).toBe(true);
        expect(validateMessageContent('', 1).valid).toBe(true);
        expect(validateMessageContent('Here are the files', 2).valid).toBe(true);

        const veryLongText = 'a'.repeat(2001);
        expect(validateMessageContent(veryLongText, 0).valid).toBe(false);
    });
});
