<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Bienvenue sur EcoPoche</title>
<style>
  body{margin:0;padding:0;background:#F3F4F6;font-family:'Segoe UI',Arial,sans-serif;color:#1F2937}
  .wrapper{max-width:560px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08)}
  .header{background:#002452;padding:28px 32px}
  .header h1{margin:0;color:#fff;font-size:20px;font-weight:700}
  .header p{margin:4px 0 0;color:#93c5fd;font-size:13px}
  .body{padding:28px 32px}
  .creds{background:#F8FAFC;border:1px solid #E5E7EB;border-radius:8px;padding:16px 20px;margin:20px 0}
  .creds .row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #F3F4F6}
  .creds .row:last-child{border-bottom:none}
  .creds .label{font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280}
  .creds .val{font-size:13px;font-weight:700;color:#002452;font-family:monospace;background:#EEF2FF;padding:3px 8px;border-radius:4px}
  .btn{display:inline-block;background:#002452;color:#fff;text-decoration:none;padding:11px 24px;border-radius:8px;font-size:13px;font-weight:600;margin-top:20px}
  .warning{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;font-size:12px;color:#92400e;margin-top:16px}
  .footer{background:#F8FAFC;padding:16px 32px;text-align:center;font-size:11px;color:#9CA3AF;border-top:1px solid #E5E7EB}
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>EcoPoche</h1>
    <p>Votre compte a été créé</p>
  </div>
  <div class="body">
    <p style="font-size:14px;margin:0 0 8px">Bonjour <strong>{{ $user->name }}</strong>,</p>
    <p style="font-size:13px;color:#4B5563;margin:0 0 20px">Un administrateur a créé votre compte sur <strong>EcoPoche</strong>. Voici vos identifiants de connexion :</p>

    <div class="creds">
      <div class="row">
        <span class="label">Adresse email</span>
        <span class="val">{{ $user->email }}</span>
      </div>
      <div class="row">
        <span class="label">Mot de passe provisoire</span>
        <span class="val">{{ $motDePasseProvisoire }}</span>
      </div>
    </div>

    <div class="warning">
      Changez votre mot de passe dès votre première connexion dans les paramètres de votre profil.
    </div>

    <a href="{{ url('/login') }}" class="btn">Se connecter à EcoPoche</a>
  </div>
  <div class="footer">
    EcoPoche &mdash; Gestion budgétaire personnelle &bull;
    Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.
  </div>
</div>
</body>
</html>
