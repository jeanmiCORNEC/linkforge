<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur LinkForge</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo img {
            height: 40px;
        }
        h1 {
            color: #0f172a;
            font-size: 24px;
            margin-bottom: 16px;
            text-align: center;
        }
        p {
            margin-bottom: 16px;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 16px;
            margin-bottom: 16px;
        }
        .footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <!-- Placeholder for logo if needed, or just text -->
                <strong style="font-size: 24px; color: #4f46e5;">LinkForge</strong>
            </div>
            
            <h1>Bienvenue à bord ! 🚀</h1>
            
            <p>Bonjour {{ $user->name }},</p>
            
            <p>Nous sommes ravis de vous compter parmi nous. Votre compte a été créé avec succès.</p>
            
            <p>Avec LinkForge, vous pouvez dès maintenant :</p>
            <ul>
                <li>Créer des liens courts et personnalisés.</li>
                <li>Organiser vos liens dans des campagnes.</li>
                <li>Suivre vos performances avec des analyses détaillées.</li>
            </ul>

            <div style="text-align: center;">
                <a href="{{ route('dashboard') }}" class="button">Accéder à mon tableau de bord</a>
            </div>

            <p>Si vous avez des questions, n'hésitez pas à répondre à cet email.</p>
            
            <p>À très vite,<br>L'équipe LinkForge</p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} LinkForge. Tous droits réservés.
        </div>
    </div>
</body>
</html>
