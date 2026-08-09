const envFileSizeMb = typeof import.meta !== 'undefined' && import.meta.env?.VITE_MAX_FILE_SIZE_MB ? Number(import.meta.env.VITE_MAX_FILE_SIZE_MB) : 25;
const envTotalSizeMb = typeof import.meta !== 'undefined' && import.meta.env?.VITE_MAX_TOTAL_SIZE_MB ? Number(import.meta.env.VITE_MAX_TOTAL_SIZE_MB) : 100;
const envMaxAttachments = typeof import.meta !== 'undefined' && import.meta.env?.VITE_MAX_ATTACHMENTS ? Number(import.meta.env.VITE_MAX_ATTACHMENTS) : 10;
const envMaxMsgLength = typeof import.meta !== 'undefined' && import.meta.env?.VITE_MAX_MESSAGE_LENGTH ? Number(import.meta.env.VITE_MAX_MESSAGE_LENGTH) : 2000;

export const MAX_FILE_SIZE = (isNaN(envFileSizeMb) ? 25 : envFileSizeMb) * 1024 * 1024;
export const MAX_TOTAL_SIZE = (isNaN(envTotalSizeMb) ? 100 : envTotalSizeMb) * 1024 * 1024;
export const MAX_ATTACHMENTS = isNaN(envMaxAttachments) ? 10 : envMaxAttachments;
export const MAX_MESSAGE_LENGTH = isNaN(envMaxMsgLength) ? 2000 : envMaxMsgLength;

export const BLOCKED_EXTENSIONS = [
    'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar',
    'cgi', 'pl', 'asp', 'aspx', 'jsp', 'sh', 'bash', 'zsh', 'cmd', 'ps1'
];

export const IMAGE_EXTENSIONS = [
    'png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'bmp', 'ico', 'avif'
];

export function getFileExtension(filename: string): string {
    if (!filename) return '';
    const parts = filename.split('.');
    if (parts.length <= 1) return '';
    return parts.pop()?.toLowerCase() || '';
}

export function isImageFile(filenameOrMime: string): boolean {
    if (!filenameOrMime) return false;
    if (filenameOrMime.startsWith('image/')) return true;
    const ext = getFileExtension(filenameOrMime);
    return IMAGE_EXTENSIONS.includes(ext);
}

export function formatFileSize(bytes: number): string {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

export interface FileValidationResult {
    valid: boolean;
    error?: string;
}

export function validateSingleFile(file: File): FileValidationResult {
    const ext = getFileExtension(file.name);

    if (BLOCKED_EXTENSIONS.includes(ext)) {
        return {
            valid: false,
            error: `Files with the '.${ext}' extension are not permitted for security reasons.`
        };
    }

    if (file.size > MAX_FILE_SIZE) {
        return {
            valid: false,
            error: `File '${file.name}' exceeds the maximum allowed file size of ${formatFileSize(MAX_FILE_SIZE)}.`
        };
    }

    return { valid: true };
}

export interface BatchValidationResult {
    validFiles: File[];
    errors: string[];
}

export function validateFilesBatch(currentFiles: File[], newFiles: File[]): BatchValidationResult {
    const validFiles: File[] = [...currentFiles];
    const errors: string[] = [];

    const availableSlots = MAX_ATTACHMENTS - currentFiles.length;
    if (availableSlots <= 0) {
        errors.push(`Maximum attachment limit reached (${MAX_ATTACHMENTS} files).`);
        return { validFiles, errors };
    }

    let currentTotalSize = currentFiles.reduce((sum, f) => sum + f.size, 0);

    for (let i = 0; i < newFiles.length; i++) {
        if (validFiles.length >= MAX_ATTACHMENTS) {
            errors.push(`Only up to ${MAX_ATTACHMENTS} attachments can be uploaded per message.`);
            break;
        }

        const file = newFiles[i];
        const validation = validateSingleFile(file);

        if (!validation.valid) {
            if (validation.error) errors.push(validation.error);
            continue;
        }

        if (currentTotalSize + file.size > MAX_TOTAL_SIZE) {
            errors.push(`Total attachment size would exceed the ${formatFileSize(MAX_TOTAL_SIZE)} payload limit.`);
            break;
        }

        // Avoid adding exact duplicates (same name, size, lastModified)
        const isDuplicate = validFiles.some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified);
        if (!isDuplicate) {
            validFiles.push(file);
            currentTotalSize += file.size;
        }
    }

    return { validFiles, errors };
}

export function validateMessageContent(content: string | null | undefined, filesCount: number): FileValidationResult {
    const trimmed = (content || '').trim();

    if (trimmed.length === 0 && filesCount === 0) {
        return {
            valid: false,
            error: 'Please enter a message or attach at least one file.'
        };
    }

    if (trimmed.length > MAX_MESSAGE_LENGTH) {
        return {
            valid: false,
            error: `Message exceeds the maximum limit of ${MAX_MESSAGE_LENGTH} characters (currently ${trimmed.length}).`
        };
    }

    return { valid: true };
}
