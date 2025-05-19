<?php
function fetchPokemonList() {
    $cacheFile = __DIR__ . '/pokemon_list_cache.json';
    $cacheTime = 86400; // 1 day

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    $url = "https://pokeapi.co/api/v2/pokemon?limit=151";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        file_put_contents($cacheFile, $response);
        return json_decode($response, true);
    }

    return null;
}

function fetchPokemonData($query) {
    $query = strtolower(trim($query));
    $cacheFile = __DIR__ . "/cache_" . md5($query) . ".json";
    $cacheTime = 3600; // 1 hour

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    $url = "https://pokeapi.co/api/v2/pokemon/" . urlencode($query);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FAILONERROR => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        file_put_contents($cacheFile, $response);
        return json_decode($response, true);
    }

    return null;
}

$pokemonList = fetchPokemonList();
$pokemonData = null;
$error = '';
$query = isset($_GET['query']) ? $_GET['query'] : '';

if ($query !== '') {
    $pokemonData = fetchPokemonData($query);
    if (!$pokemonData) {
        $error = "❌ Pokémon not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pokémon Info App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f2fcfe, #1c92d2);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 720px;
            margin: 40px auto;
            background: #fff;
            padding: 30px 25px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #1c92d2;
            margin-bottom: 25px;
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
        }

        select {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .error {
            color: #d9534f;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }

        .pokemon-card {
            text-align: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 12px;
        }

        .pokemon-card img {
            max-width: 180px;
            margin-bottom: 10px;
            transition: transform 0.2s;
        }

        .pokemon-card img:hover {
            transform: scale(1.05);
        }

        h2 {
            margin: 10px 0 5px;
            color: #333;
        }

        .type-badge {
            display: inline-block;
            background: #1c92d2;
            color: #fff;
            padding: 6px 12px;
            margin: 5px 5px;
            border-radius: 20px;
            font-size: 14px;
            text-transform: uppercase;
        }

        .pokemon-card p {
            text-align: left;
            max-width: 400px;
            margin: 10px auto;
        }

        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .nav-buttons a button {
            padding: 10px 20px;
            background-color: #1c92d2;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .nav-buttons a button:hover {
            background-color: #155a91;
        }

        @media (max-width: 500px) {
            .nav-buttons {
                flex-direction: column;
                gap: 10px;
            }

            select {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Pokémon Info App</h1>
    <form method="get">
        <select name="query" onchange="this.form.submit()">
            <option value="">Select Pokémon</option>
            <?php if ($pokemonList): ?>
                <?php foreach ($pokemonList['results'] as $index => $poke): 
                    $id = $index + 1;
                    $selected = ($query == $poke['name'] || $query == $id) ? 'selected' : '';
                ?>
                    <option value="<?= $id ?>" <?= $selected ?>><?= ucfirst($poke['name']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </form>

    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <?php if ($pokemonData): ?>
        <div class="pokemon-card">
            <img src="<?= $pokemonData['sprites']['front_default'] ?>" alt="<?= $pokemonData['name'] ?>">
            <h2><?= ucfirst($pokemonData['name']) ?> (ID: <?= $pokemonData['id'] ?>)</h2>

            <p><strong>Types:</strong><br>
                <?php foreach ($pokemonData['types'] as $type): ?>
                    <span class="type-badge"><?= ucfirst($type['type']['name']) ?></span>
                <?php endforeach; ?>
            </p>

            <p><strong>Stats:</strong><br>
                <?php foreach ($pokemonData['stats'] as $stat): ?>
                    <?= ucfirst($stat['stat']['name']) ?>: <?= $stat['base_stat'] ?><br>
                <?php endforeach; ?>
            </p>

            <div class="nav-buttons">
                <?php if ($pokemonData['id'] > 1): ?>
                    <a href="?query=<?= $pokemonData['id'] - 1 ?>"><button>&larr; Previous</button></a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
                <?php if ($pokemonData['id'] < 151): ?>
                    <a href="?query=<?= $pokemonData['id'] + 1 ?>"><button>Next &rarr;</button></a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
