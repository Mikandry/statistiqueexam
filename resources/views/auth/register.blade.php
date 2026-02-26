<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription saisisseur</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(150deg, #f8fafc, #dbeafe);
            color: #0f172a;
        }
        .card {
            width: min(94vw, 480px);
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        }
        h1 { margin: 0 0 6px; font-size: 1.4rem; }
        p { margin: 0 0 16px; color: #475569; font-size: 0.92rem; }
        label { display: block; font-size: 0.9rem; margin: 10px 0 6px; font-weight: 600; }
        input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.95rem;
        }
        button {
            width: 100%;
            margin-top: 16px;
            border: 0;
            border-radius: 10px;
            padding: 10px 12px;
            background: #0f766e;
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
        .links { margin-top: 12px; }
    </style>
</head>
<body>
<main class="card">
    <h1>Créer un saisisseur</h1>
    <p>Enregistrer un nouveau compte de saisie.</p>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <label for="name">Nom complet</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="password" required>

        <label for="password_confirmation">Confirmer le mot de passe</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button type="submit">Créer le compte</button>
    </form>

    <div class="links">
        <a href="{{ route('login') }}">J'ai déjà un compte</a>
    </div>
</main>
</body>
</html>
