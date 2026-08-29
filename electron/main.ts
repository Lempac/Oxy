import { app, BrowserWindow, ipcMain, systemPreferences, desktopCapturer } from 'electron';
import path from 'path';

// Disable hardware acceleration to prevent SIGILL crashes on non-standard GPU/CPU drivers on Linux/NixOS
if (process.platform === 'linux') {
  app.disableHardwareAcceleration();
  app.commandLine.appendSwitch('no-sandbox');
  app.commandLine.appendSwitch('disable-gpu-sandbox');
}

let mainWindow: BrowserWindow | null = null;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    minWidth: 800,
    minHeight: 600,
    title: 'Oxy',
    autoHideMenuBar: true,
    webPreferences: {
      preload: path.join(__dirname, 'preload.cjs'),
      nodeIntegration: false,
      contextIsolation: true,
      webSecurity: true,
    },
  });

  // Hide top menu bar visually on Linux & Windows without disabling keybindings
  mainWindow.setMenuBarVisibility(false);

  const devServerUrl = process.env.VITE_DEV_SERVER_URL;
  if (devServerUrl) {
    mainWindow.loadURL(devServerUrl);
  } else {
    mainWindow.loadFile(path.join(__dirname, '../dist/index.html'));
  }

  // Register keyboard shortcuts (F12, Ctrl+Shift+I for DevTools, Ctrl+R / F5 for reload)
  mainWindow.webContents.on('before-input-event', (event, input) => {
    if (input.type === 'keyDown') {
      const isDevTools = input.key === 'F12' || (input.control && input.shift && input.key.toLowerCase() === 'i') || (input.meta && input.alt && input.key.toLowerCase() === 'i');
      const isReload = (input.control && input.key.toLowerCase() === 'r') || input.key === 'F5' || (input.meta && input.key.toLowerCase() === 'r');
      if (isDevTools) {
        mainWindow?.webContents.toggleDevTools();
        event.preventDefault();
      } else if (isReload) {
        mainWindow?.webContents.reload();
        event.preventDefault();
      }
    }
  });

  // Handle native media permission requests for LiveKit voice/video
  mainWindow.webContents.session.setPermissionRequestHandler((_webContents, permission, callback) => {
    const allowedPermissions = ['media', 'audioCapture', 'videoCapture', 'notifications', 'display-capture'];
    if (allowedPermissions.includes(permission)) {
      callback(true);
    } else {
      callback(false);
    }
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

app.whenReady().then(async () => {
  // Auto grant system media permissions on macOS if supported
  if (process.platform === 'darwin') {
    try {
      await systemPreferences.askForMediaAccess('microphone');
      await systemPreferences.askForMediaAccess('camera');
    } catch {
      // Ignore if systemPreferences not available
    }
  }

  createWindow();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

// IPC Handler for desktop screen sources (Screen Sharing)
ipcMain.handle('get-screen-sources', async () => {
  const sources = await desktopCapturer.getSources({ types: ['window', 'screen'] });
  return sources.map((s) => ({
    id: s.id,
    name: s.name,
    thumbnail: s.thumbnail.toDataURL(),
  }));
});
