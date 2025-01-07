<?php
    $limitePagina = 10;
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $desplazamiento = ($pagina - 1) * $limitePagina;
    
    $API_URL = "https://pokeapi.co/api/v2/pokemon?limit=$limitePagina&offset=$desplazamiento";

    # inicializar una nueva sesion de curl
    $curl = curl_init($API_URL);

    // indicar que queremos recibir el resultado de la peticion y no mostrarle en pantalla
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // ejecutar la petición y guardar el resultado
    $resultado = curl_exec($curl);

    $data = json_decode($resultado,true);
    curl_close($curl);

    $curlMultiple = curl_multi_init();

    $curlHandles = [];
    $responses = [];

    foreach($data['results'] as $index=>$pokemon){
        $curlHandles[$index] = curl_init($pokemon['url']);
        curl_setopt($curlHandles[$index],CURLOPT_RETURNTRANSFER,true);

        curl_multi_add_handle($curlMultiple,$curlHandles[$index]);
    }

    $active = null;
    // en paralelo
    do{
        curl_multi_exec($curlMultiple,$active);
        usleep(5000);
    }while($active>0);

    // guardar respuestas en cada solicitud
    foreach($curlHandles as $index=>$handle){
        $responses[$index] = json_decode(curl_multi_getcontent($handle),true);
        curl_multi_remove_handle($curlMultiple,$handle);
    }   

    // cerramos el handle multi curl
    curl_multi_close($curlMultiple);

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pokeapi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    <div >
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/98/International_Pokémon_logo.svg/1200px-International_Pokémon_logo.svg.png" class="mx-auto d-block" style="width:200px"></img>
    </div>
    <div class="container">
    <div class="row row-cols-1 row-cols-md-3 g-4" >
        <?php foreach($data['results'] as $index=>$pokemon):
            $detallePokemon = $responses[$index];
            $imagenPokemon = $detallePokemon['sprites']['other']['official-artwork'];
        ?>
        <div class="col">
            <div class="card h-100" >
                <img src="<?=$imagenPokemon['front_default'];?>" class="card-img-top" alt="..." style="height:200px;width:200px;">
                <div class="card-body">
                    <h5 class="card-title"><?= ucfirst($pokemon['name'])?></h5>
                    <p class="card-text">Url: <?= $pokemon['url']?></p>
                </div>
                <div class="card-footer" style="background:crimson;color:white">
                    <small class="text-body-secondary">No: <?= $detallePokemon['id']?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<nav aria-label="Page navigation example">
<ul class="pagination justify-content-center">
<?php
    if($pagina >1){
        echo '<li class="page-item">';
        echo "<a href='?page="  .($pagina-1)."' class='page-link'>Anterior</a></li>";
    }
    echo "<li class='page-item'><a href='?page="  .($pagina+1).   "' class='page-link'>Siguiente</a></li>";      
?>
    </ul>
    </nav>
    <h2 class="text-center">Página: <?= $pagina?></h2>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
</body>
</html>