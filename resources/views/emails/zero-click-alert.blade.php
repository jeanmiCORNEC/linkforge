<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte Trafic</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #fff1f2; color: #334155; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; margin-top: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-top: 4px solid #e11d48; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #1e293b; text-decoration: none; }
        h1 { font-size: 20px; color: #be123c; margin-bottom: 20px; text-align: center; }
        .message { line-height: 1.6; margin-bottom: 30px; color: #475569; }
        .btn { display: inline-block; background-color: #e11d48; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; text-align: center; display: block; margin: 0 auto; width: fit-content; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ config('app.url') }}" class="logo">LinkForge</a>
        </div>

        <h1>⚠️ Alerte Trafic</h1>

        <div class="message">
            <p>Bonjour {{ $user->name }},</p>
            <p>Nous avons remarqué que vos liens n'ont reçu <strong>aucun clic depuis {{ $daysSinceLastClick }} jours</strong>.</p>
            <p>Cela peut être normal si vous n'avez pas partagé de contenu récemment, mais nous voulions nous assurer que vos liens fonctionnent comme prévu et que vos campagnes sont toujours actives.</p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn">Vérifier mes liens</a>

        <div class="footer">
            <p>Cet email est une alerte automatique envoyée lorsque votre trafic chute anormalement.</p>
            <p>&copy; {{ date('Y') }} LinkForge. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
