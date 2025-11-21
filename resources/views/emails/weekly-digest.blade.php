<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif Hebdomadaire</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; margin-top: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #4f46e5; text-decoration: none; }
        h1 { font-size: 20px; color: #1e293b; margin-bottom: 20px; text-align: center; }
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: #f1f5f9; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #0f172a; display: block; }
        .stat-label { font-size: 14px; color: #64748b; }
        .top-links { margin-bottom: 30px; }
        .top-links h2 { font-size: 16px; color: #334155; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 15px; }
        .link-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .link-title { font-weight: 500; color: #1e293b; }
        .link-clicks { font-weight: bold; color: #4f46e5; }
        .btn { display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; text-align: center; display: block; margin: 0 auto; width: fit-content; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ config('app.url') }}" class="logo">LinkForge</a>
        </div>

        <h1>Vos performances de la semaine dernière</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-value">{{ $stats['total_clicks'] }}</span>
                <span class="stat-label">Clics Totaux</span>
            </div>
            <div class="stat-card">
                <span class="stat-value">{{ $stats['active_links'] }}</span>
                <span class="stat-label">Liens Actifs</span>
            </div>
        </div>

        @if(count($stats['top_links']) > 0)
        <div class="top-links">
            <h2>Top 3 Liens</h2>
            @foreach($stats['top_links'] as $link)
            <div class="link-item">
                <span class="link-title">{{ $link['title'] }}</span>
                <span class="link-clicks">{{ $link['clicks'] }} clics</span>
            </div>
            @endforeach
        </div>
        @endif

        <a href="{{ route('dashboard') }}" class="btn">Voir mon Dashboard</a>

        <div class="footer">
            <p>Vous recevez cet email car vous avez des liens actifs sur LinkForge.</p>
            <p>&copy; {{ date('Y') }} LinkForge. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
