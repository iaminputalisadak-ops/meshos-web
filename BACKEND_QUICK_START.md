# Fix "Backend not reachable"

## Option A: One command (recommended)

1. **Stop** any running `npm start` (Ctrl+C).
2. In the project folder run:
   ```bash
   npm install
   npm run dev
   ```
3. Wait until the browser opens. Use **http://localhost:3000**.

`npm run dev` starts both the PHP backend (port 8888) and the React app (port 3000). The app talks to the backend via the dev server proxy (no CORS).

**If you see "php not found":** use Option B.

---

## Option B: Two windows (no PHP in PATH)

1. **Window 1:** Double-click **`backend\start-server.bat`** (or in a terminal: `cd backend` then `start-server.bat`). Leave it open.
2. **Window 2:** Run **`npm start`**.
3. Open **http://localhost:3000**.

---

## Check that the backend is running

Open **http://localhost:8888/api/health.php** in your browser. You should see:
```json
{"ok":true,"message":"Backend is running"}
```

If that page does not load, the backend is not running — use Option A or B above.

---

**Note:** MySQL (e.g. from XAMPP) must be running for products and promoter signup. Only the web server is replaced by the PHP built-in server.
