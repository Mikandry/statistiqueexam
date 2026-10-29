<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion saisisseur</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('soe-favicon.svg') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(140deg, #f1f5f9, #e2e8f0);
            color: #0f172a;
        }
        .card {
            width: min(92vw, 430px);
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        }
        h1 { margin: 0 0 6px; font-size: 1.4rem; }
        p { margin: 0 0 16px; color: #475569; font-size: 0.92rem; }
        label { display: block; font-size: 0.9rem; margin: 10px 0 6px; font-weight: 600; }
        input[type="email"], input[type="password"] {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.95rem;
        }
        .row {
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        button {
            width: 100%;
            margin-top: 14px;
            border: 0;
            border-radius: 10px;
            padding: 10px 12px;
            background: #1d4ed8;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        a { color: #1d4ed8; text-decoration: none; font-size: 0.9rem; }
        .error {
            margin-bottom: 10px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 0.88rem;
        }
    </style>
</head>
<body>
<main class="card">
    <h1>Connexion saisisseur</h1>
    <p>Accès à la saisie de répartition.</p>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="password" required>

        <div class="row">
            <label style="margin:0; font-weight:500; display:flex; gap:8px; align-items:center;">
                <input type="checkbox" name="remember" value="1"> Se souvenir
            </label>
            <a href="{{ route('register') }}">Créer un compte</a>
        </div>

        <button type="submit">Se connecter</button>
    </form>
</main>
</body>
</html>
