from fastapi import FastAPI, Request, Form
from fastapi.responses import HTMLResponse, RedirectResponse, PlainTextResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from itsdangerous import URLSafeTimedSerializer, BadSignature, SignatureExpired
import os
import docker

# Configure static files and templates
app = FastAPI()
app.mount("/static", StaticFiles(directory="static"), name="static")
templates = Jinja2Templates(directory="templates")

COOKIE_NAME = "gateway_session"

SECRET_KEY = os.getenv("AUTH_SECRET")
if not SECRET_KEY:
    raise RuntimeError("AUTH_SECRET is not set")

serializer = URLSafeTimedSerializer(SECRET_KEY)

USERS = {
    "siya": "SiyaPass123!",
    "nkosi": "NkosiPass123!",
    "thina": "admin1234",
    "test": "password",
}

COOKIE_NAME = "gw_session"
COOKIE_MAX_AGE = 60 * 60 * 8

client = docker.from_env()

LOGIN_HTML = r"""<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<style>
:root {
  /* Dark Theme (Default) */
  --bg-primary: linear-gradient(135deg, #0b1220 0%, #1a1a2e 100%);
  --bg-secondary: rgba(255, 255, 255, 0.08);
  --bg-input: rgba(255, 255, 255, 0.05);
  --bg-input-focus: rgba(255, 255, 255, 0.1);
  --bg-error: #dc2626;
  --bg-button: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  --bg-button-hover: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
  
  --text-primary: #ffffff;
  --text-secondary: #e5e7eb;
  --text-muted: #d1d5db;
  --text-placeholder: #9ca3af;
  
  --border-primary: rgba(255, 255, 255, 0.1);
  --border-focus: #3b82f6;
  --border-error: #ef4444;
  
  --shadow-primary: 0 20px 40px rgba(0, 0, 0, 0.3);
  --shadow-button: 0 4px 12px rgba(59, 130, 246, 0.4);
  --shadow-focus: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

[data-theme="light"] {
  /* Light Theme */
  --bg-primary: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  --bg-secondary: rgba(255, 255, 255, 0.9);
  --bg-input: rgba(0, 0, 0, 0.05);
  --bg-input-focus: rgba(0, 0, 0, 0.1);
  --bg-error: #dc2626;
  --bg-button: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  --bg-button-hover: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
  
  --text-primary: #1e293b;
  --text-secondary: #334155;
  --text-muted: #64748b;
  --text-placeholder: #94a3b8;
  
  --border-primary: rgba(0, 0, 0, 0.1);
  --border-focus: #3b82f6;
  --border-error: #ef4444;
  
  --shadow-primary: 0 20px 40px rgba(0, 0, 0, 0.1);
  --shadow-button: 0 4px 12px rgba(59, 130, 246, 0.4);
  --shadow-focus: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

* {
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background: var(--bg-primary);
  color: var(--text-secondary);
  margin: 0;
  padding: 0;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s ease, color 0.3s ease;
}

.wrap {
  width: 100%;
  max-width: 400px;
  margin: 20px;
  padding: 40px 30px;
  background: var(--bg-secondary);
  border-radius: 16px;
  box-shadow: var(--shadow-primary);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border-primary);
  position: relative;
  transition: background 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}

.theme-toggle {
  position: absolute;
  top: 20px;
  right: 20px;
  background: none;
  border: none;
  color: var(--text-secondary);
  font-size: 20px;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

.theme-toggle:hover {
  background: var(--bg-input);
  transform: scale(1.05);
}

h2 {
  text-align: center;
  margin: 0 0 30px 0;
  font-size: 28px;
  font-weight: 600;
  color: var(--text-primary);
  transition: color 0.3s ease;
}

.err {
  background: var(--bg-error);
  color: #ffffff;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  border-left: 4px solid var(--border-error);
  font-size: 14px;
}

form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

label {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-muted);
  margin-bottom: 4px;
  transition: color 0.3s ease;
}

input {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid var(--border-primary);
  border-radius: 8px;
  background: var(--bg-input);
  color: var(--text-primary);
  font-size: 16px;
  transition: all 0.3s ease;
}

input:focus {
  outline: none;
  border-color: var(--border-focus);
  background: var(--bg-input-focus);
  box-shadow: var(--shadow-focus);
}

input::placeholder {
  color: var(--text-placeholder);
}

button[type="submit"] {
  width: 100%;
  padding: 14px 16px;
  background: var(--bg-button);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 10px;
}

button[type="submit"]:hover {
  background: var(--bg-button-hover);
  transform: translateY(-1px);
  box-shadow: var(--shadow-button);
}

button[type="submit"]:active {
  transform: translateY(0);
}

@media (max-width: 480px) {
  .wrap {
    margin: 10px;
    padding: 30px 20px;
  }
  
  h2 {
    font-size: 24px;
  }
}
</style>
</head>
<body>
<div class="wrap">
<button class="theme-toggle" id="theme-toggle" title="Toggle theme">🌙</button>
<h2>Gateway Login</h2>
{error_block}
<form method="post" action="/login">
<div class="form-group">
<label for="username">Username</label>
<input id="username" name="username" type="text" required autocomplete="username" placeholder="Enter your username">
</div>
<div class="form-group">
<label for="password">Password</label>
<input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password">
</div>
<button type="submit">Sign In</button>
</form>
</div>

<script>
const themeToggle = document.getElementById('theme-toggle');
const html = document.documentElement;

// Load saved theme
const savedTheme = localStorage.getItem('theme') || 'dark';
html.setAttribute('data-theme', savedTheme);
themeToggle.textContent = savedTheme === 'dark' ? '🌙' : '☀️';

// Toggle theme
themeToggle.addEventListener('click', () => {
  const currentTheme = html.getAttribute('data-theme');
  const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
  
  html.setAttribute('data-theme', newTheme);
  themeToggle.textContent = newTheme === 'dark' ? '🌙' : '☀️';
  
  localStorage.setItem('theme', newTheme);
});
</script>
</body>
</html>
"""

def make_cookie(username: str) -> str:
    return serializer.dumps({"u": username})

def read_cookie(value: str):
    try:
        return serializer.loads(value, max_age=COOKIE_MAX_AGE)["u"]
    except (BadSignature, SignatureExpired):
        return None

@app.get("/health")
def health():
    return PlainTextResponse("ok")

@app.get("/login")
def login_get(request: Request, err: str | None = None):
    error = "Invalid login" if err else None
    return templates.TemplateResponse("login.html", {"request": request, "error": error})

@app.post("/login")
def login_post(username: str = Form(...), password: str = Form(...)):
    if USERS.get(username) != password:
        return RedirectResponse("/login?err=1", 303)

    resp = RedirectResponse("/", 303)
    resp.set_cookie(
        COOKIE_NAME,
        make_cookie(username),
        httponly=True,
        samesite="lax",
        secure=False,
        max_age=COOKIE_MAX_AGE,
        path="/"
    )
    return resp

@app.get("/auth")
def auth_check(request: Request):
    token = request.cookies.get(COOKIE_NAME)
    if not token:
        return PlainTextResponse("no session", status_code=401)

    try:
        data = serializer.loads(token, max_age=60*60*24)  # 24h; match what you used
        username = data.get("u")
        if not username:
            return PlainTextResponse("bad session", status_code=401)

        # IMPORTANT: send the username back to Nginx as a header
        resp = PlainTextResponse("ok", status_code=200)
        resp.headers["X-Auth-User"] = username
        return resp

    except (BadSignature, SignatureExpired):
        return PlainTextResponse("invalid session", status_code=401)


@app.get("/logout")
def logout():
    resp = RedirectResponse(url="/login", status_code=303)
    # delete cookie (must match cookie name + path you used when setting it)
    resp.delete_cookie("gateway_session", path="/")
    return resp

@app.get("/")
def dashboard(request: Request):
    try:
        containers = client.containers.list(all=True)
        
        # Group containers by status
        running_containers = []
        stopped_containers = []
        other_containers = []
        
        for container in containers:
            if container.status == 'running':
                running_containers.append(container)
            elif container.status == 'exited':
                stopped_containers.append(container)
            else:
                other_containers.append(container)
        
        # Prepare container data for template
        def prepare_container_data(container):
            image = container.image.tags[0] if container.image.tags else 'N/A'
            ports = []
            if container.ports:
                for container_port, host_bindings in container.ports.items():
                    if host_bindings:
                        for binding in host_bindings:
                            host_port = binding.get('HostPort', 'N/A')
                            ports.append(f"{container_port} → {host_port}")
                    else:
                        ports.append(f"{container_port} (internal)")
            return {
                'name': container.name,
                'status': container.status,
                'image': image,
                'ports': ', '.join(ports) if ports else None,
                'id': container.short_id
            }

        context = {
            "request": request,
            "total_containers": len(containers),
            "running_count": len(running_containers),
            "stopped_count": len(stopped_containers),
            "running_containers": [prepare_container_data(c) for c in running_containers],
            "stopped_containers": [prepare_container_data(c) for c in stopped_containers],
            "other_containers": [prepare_container_data(c) for c in other_containers],
            "error": None
        }

    except Exception as e:
        context = {
            "request": request,
            "error": str(e)
        }

    return templates.TemplateResponse("dashboard.html", context)