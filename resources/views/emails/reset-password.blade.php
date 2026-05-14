<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Réinitialisation de mot de passe — EcoPoche</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #F8FAFC; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 32px auto; background: #fff; border-radius: 16px; border: 1px solid #E5E7EB; overflow: hidden; }
        .header { background: #002452; padding: 28px 32px; text-align: center; }
        .header h1 { color: #fff; font-size: 20px; font-weight: 800; margin: 0; letter-spacing: -0.3px; }
        .header p { color: rgba(255,255,255,0.6); font-size: 12px; margin: 4px 0 0; }
        .body { padding: 28px 32px; }
        .body p { color: #374151; font-size: 14px; line-height: 1.6; margin: 0 0 16px; }
        .btn { display: inline-block; background: #002452; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-weight: 700; font-size: 14px; margin: 8px 0 16px; }
        .warning { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 12px 16px; color: #92400E; font-size: 12px; }
        .footer { padding: 16px 32px; border-top: 1px solid #F3F4F6; text-align: center; }
        .footer p { color: #9CA3AF; font-size: 11px; margin: 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>EcoPoche</h1>
        <p>Réinitialisation de mot de passe</p>
    </div>
    <div class="body">
        <p>Bonjour <strong>{{ $user->name }}</strong>,</p>
        <p>Nous avons reçu une demande de réinitialisation du mot de passe associé à votre compte EcoPoche.</p>
        <p>Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe. Ce lien est valable <strong>60 minutes</strong>.</p>

        <div style="text-align:center; margin: 20px 0;">
            <a href="{{ $url }}" class="btn">Réinitialiser mon mot de passe</a>
        </div>

        <div class="warning">
            <strong>⚠ Vous n'avez pas fait cette demande ?</strong><br/>
            Ignorez cet email. Votre mot de passe ne sera pas modifié.
        </div>

        <p style="margin-top:16px; font-size:12px; color:#6B7280;">
            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br/>
            <span style="word-break:break-all; color:#002452;">{{ $url }}</span>
        </p>
    </div>
    <div class="footer">
        <p>EcoPoche · Gestion Budgétaire Personnelle</p>
    </div>
</div>
</body>
</html>
