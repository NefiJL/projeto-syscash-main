<?php
session_start();
require_once("valida_acesso2.php");
require_once("conexao.php");
require_once("conta_receber_filtro.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["acao"])) {
        return;
    }

    switch ($_POST["acao"]) {
        case "adicionar":
            try {
                $errosAux = "";

                $registro = new stdClass();
                $registro = json_decode($_POST['registro']);
                validaDados($registro);

                $sql = "INSERT INTO conta_receber(descricao, favorecido, valor, data_vencimento, categoria_id, usuario_id) VALUES (?, ?, ?, ?, ?, ?)";
                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
                $pre = $conexao->prepare($sql);
                $pre->execute(array(
                    $registro->descricao_contareceber,
                    $registro->favorecido_contareceber,
                    $registro->valor_contareceber,
                    $registro->datavencimento_contareceber,
                    $registro->categoria_id_contareceber,
                    $registro->usuario_id_contareceber
                ));
                print json_encode($conexao->lastInsertId());
            } catch (Exception $e) {
                if (isset($_SESSION["erros"])) {
                    foreach ($_SESSION["erros"] as $chave => $valor) {
                        $errosAux .= $valor . "<br>";
                    }
                }
                $errosAux .= $e->getMessage();
                unset($_SESSION["erros"]);
                echo "Erro: " . $errosAux . "<br>";
            }
            break;
        case "editar":
            try {
                $errosAux = "";

                $registro = new stdClass();
                $registro = json_decode($_POST['registro']);
                validaDados($registro);

                $sql = "UPDATE conta_receber SET descricao = ?, favorecido = ?, valor = ?, data_vencimento = ?, categoria_id = ? WHERE id = ?";
                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
                $pre = $conexao->prepare($sql);
                $pre->execute(array(
                    $registro->descricao_contareceber,
                    $registro->favorecido_contareceber,
                    $registro->valor_contareceber,
                    $registro->datavencimento_contareceber,
                    $registro->categoria_id_contareceber,
                    $registro->id_contareceber
                ));
                print json_encode(1);
            } catch (Exception $e) {
                if (isset($_SESSION["erros"])) {
                    foreach ($_SESSION["erros"] as $chave => $valor) {
                        $errosAux .= $valor . "<br>";
                    }
                }
                $errosAux .= $e->getMessage();
                unset($_SESSION["erros"]);
                echo "Erro: " . $errosAux . "<br>";
            }
            break;
        case "excluir":
            try {
                $registro = new stdClass();
                $registro = json_decode($_POST["registro"]);

                $sql = "DELETE FROM conta_receber WHERE id = ?";
                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
                $pre = $conexao->prepare($sql);
                $pre->execute(array(
                    $registro->id
                ));

                print json_encode(1);
            } catch (Exception $e) {
                echo "Erro: " . $e->getMessage() . "<br>";
            }
            break;
        case 'buscar':
            try {
                $registro = new stdClass();
                $registro = json_decode($_POST["registro"]);

                $sql = "SELECT * FROM conta_receber WHERE id = ?";
                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
                $pre = $conexao->prepare($sql);
                $pre->execute(array(
                    $registro->id
                ));

                print json_encode($pre->fetchAll(PDO::FETCH_ASSOC));
            } catch (Exception $e) {
                echo "Erro: " . $e->getMessage() . "<br>";
            }
            break;
        case 'grafico':
            try {
                $ano = filter_var($_POST["ano"], FILTER_VALIDATE_INT);
                $usuario_id = filter_var($_POST["usuario"], FILTER_VALIDATE_INT);
                $receber = null;
                $receber_aux = [];
                $linhas = [];
                $retorno = [];

                $meses = [
                    1 => 'Janeiro',
                    2 => 'Fevereiro',
                    3 => 'Março',
                    4 => 'Abril',
                    5 => 'Maio',
                    6 => 'Junho',
                    7 => 'Julho',
                    8 => 'Agosto',
                    9 => 'Setembro',
                    10 => 'Outubro',
                    11 => 'Novembro',
                    12 => 'Dezembro'
                ];

                $sql = "SELECT EXTRACT(MONTH FROM data_vencimento) AS mes, SUM(valor) AS valor FROM conta_receber WHERE usuario_id = ? AND EXTRACT(YEAR FROM data_vencimento) = ? GROUP BY mes ORDER BY mes";

                $conexao = new PDO("mysql:host=" . SERVIDOR . ";dbname=" . BANCO, USUARIO, SENHA);
                $pre = $conexao->prepare($sql);
                $pre->execute(array(
                    $usuario_id,
                    $ano
                ));

                $receber = $pre->fetchAll(PDO::FETCH_ASSOC);

                for ($i = 0; $i < count($receber); $i++) {
                    $linha = $receber[$i];

                    if (array_key_exists($linha["mes"], $meses)) {
                        $linhas[$meses[$linha["mes"]]] = $linha["valor"];
                    }
                }

                if (count($linhas) < 12) {
                    for ($i = 1; $i < 13; $i++) {
                        if (array_key_exists($meses[$i], $linhas)) {
                            $receber_aux[$meses[$i]] = $linhas[$meses[$i]];
                        } else {
                            $receber_aux[$meses[$i]] = 0;
                        }
                    }
                    $linhas = $receber_aux;
                }

                foreach ($linhas as $key => $value) {
                    $retorno[] = array(
                        "mes" => $key,
                        "valor" => $value
                    );
                }

                print json_encode($retorno);
            } catch (Exception $e) {
                echo "Erro: " . $e->getMessage() . "<br>";
            }
            break;
    }
}
?>
