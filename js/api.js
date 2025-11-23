// js/api.js
let CSRF = null;
async function loadCsrf() {
  const r = await fetch('/api/csrf.php', { credentials: 'same-origin' });
  const j = await r.json();
  CSRF = j.csrf;
}

const API = {
  async login(email, password) {
    const fd = new FormData();
    fd.append('email', email);
    fd.append('password', password);
    const res = await fetch('/api/auth.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-CSRF-Token': CSRF },
      body: fd
    });
    return res.json();
  },
  async getFields() {
  const res = await fetch('/api/fields.php', { method: 'GET', credentials: 'same-origin' });
  const data = await res.json();
  // Normalise pour app.js
  return data && data.data ? data : { success: true, data }; 
}
,
  async updateField(fieldId, value) {
    const res = await fetch('/api/fields.php', {
      method: 'PUT',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({ field_id: fieldId, field_value: value })
    });
    return res.json();
  }
};

// Initialisation sans top-level await
window.API_READY = (async () => { await loadCsrf(); window.API = API; })();
