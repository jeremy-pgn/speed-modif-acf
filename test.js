// Charge le jeton CSRF (api.js)
let CSRF = null;
async function loadCsrf() {
  const r = await fetch("/api/csrf.php", { credentials: "same-origin" });
  const j = await r.json();
  CSRF = j.csrf;
}
const API = {
  async login(email, password) {
    const fd = new FormData();
    fd.append("email", email);
    fd.append("password", password);
    const res = await fetch("/api/auth.php", {
      method: "POST",
      credentials: "same-origin",
      headers: { "X-CSRF-Token": CSRF },
      body: fd,
    });
    return res.json();
  },
  async updateField(fieldId, value) {
    const res = await fetch("/api/fields.php", {
      method: "PUT",
      credentials: "same-origin",
      headers: { "Content-Type": "application/json", "X-CSRF-Token": CSRF },
      body: JSON.stringify({ field_id: fieldId, field_value: value }),
    });
    return res.json();
  },
};

//Jeton généré serveur, transmis en header X‑CSRF‑Token et validé côté serveur sur les méthodes d’écriture

//credentials: 'same-origin' pour envoyer automatiquement le cookie de session avec la requête.


