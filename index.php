<!DOCTYPE html>
<html lang="en">
<head>
    <title>Calculadora de Parcelas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php
if (count($_POST) == 0 or @$_POST['reset']) {
    $principal = 100;
    $number    = 2;
    $rate      = 2.99;
    $payment   = 0;
} else {
    $principal = $_POST['principal'];
    $number    = $_POST['number'];
    $rate      = 2.99;
    $payment   = 0;
}
?>

<div class="container-fluid">
    <div class="row">
            <div id="global" >

                <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
                    <table class="table">
                            <tr>
                                <td>Valor Venda</td><td><input type="number" name="principal" value="<?php echo $principal ?>" >
                                    <?php
                                    if (isset($error['principal'])) {
                                        echo '<p class="error">' .$error['principal'] .'</p>';
                                    } // if
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Nro de Parcelas</td><td><input type="number" name="number" value="<?php echo $number ?>" >
                                    <?php
                                    if (isset($error['number'])) {
                                        echo '<p class="error">' .$error['number'] .'</p>';
                                    } // if
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <?php $payment = calc_payment($principal,$number,$rate,2); ?>
                                <td>Valor Parcela <b><?php echo "R$ ".$payment ?></b></td><td>
                                <?php
                                if (isset($error['payment'])) {
                                    echo '<p class="error">' .$error['payment'] .'</p>';
                                } // if
                                ?>
                                <input type="submit" name="button4" value="Calcular" ></td>
                            </tr>
                    </table>
                </form>

                <table border="1" class="table">
                    <tr>
                        <td>Cartao de Debito</td>
                        <td>1 dia</td>
                        <td>2.39</td>
                        <td align="right"><?php echo CartaoDebito($principal,2.39); ?></td>
                    </tr>
                </table>
                <br>
                <table border="1" class="table">
                    <tr>
                        <td>Cartao de Credito Direto</td>
                        <td>2 dias</td>
                        <td>4.39</td>
                        <td align="right"><?php echo CartaoDebito($principal,4.39); ?></td>
                    </tr>
                </table>
                <br>
                <?php

                echo CartaoCredito($principal,$rate,$payment)."<br>";

                echo Internet($principal,$rate,$payment)."<br>";

                ?>

            </div>
    </div>
</div>

<?php

function calc_payment($pv, $payno, $int, $accuracy)
{
    $int    = $int / 100;
    $value1 = $int * pow((1 + $int), $payno);
    $value2 = pow((1 + $int), $payno) - 1;
    $pmt    = $pv * ($value1 / $value2);
    $pmt    = number_format($pmt, $accuracy, ".", "");
    return $pmt;
}

function CartaoDebito($pv,$rate){
    $valor = 0;
    $valor = $pv - ($pv / 100) * $rate;
    return number_format($valor,   2, ".", ",");
}

function CartaoCredito($balance, $rate, $payment)
{

    echo '<table border="1" class="table">';
    echo '<td colspan="5">Cartao de Credito Parcelado 2 dias<br> (Juros '.$rate.'% / Mes)<br>Minimo R$5,00 por parcela.</td>';
    echo '<colgroup align="right" width="20">';
    echo '<colgroup align="right" width="115">';
    echo '<colgroup align="right" width="115">';
    echo '<colgroup align="right" width="115">';
    echo '<colgroup align="right" width="115">';
    echo '<tr><th>#</th><th>PARCELA</th><th>JUROS</th><th>AMORTIZADO</th><th>SALDO</th></tr>';

    $count = 0;
    do {
        $count++;

        // calculate interest on outstanding balance
        $interest = $balance * $rate/100;

        // what portion of payment applies to principal?
        $principal = $payment - $interest;

        // watch out for balance < payment
        if ($balance < $payment) {
            $principal = $balance;
            $payment   = $interest + $principal;
        } // if

        // reduce balance by principal paid
        $balance = $balance - $principal;

        // watch for rounding error that leaves a tiny balance
        if ($balance < 0) {
            $principal = $principal + $balance;
            $interest  = $interest - $balance;
            $balance   = 0;
        } // if

        echo "<tr>";
        echo "<td>$count</td>";
        echo "<td>" .number_format($payment,   2, ".", ",") ."</td>";
        echo "<td>" .number_format($interest,  2, ".", ",") ."</td>";
        echo "<td>" .number_format($principal, 2, ".", ",") ."</td>";
        echo "<td>" .number_format($balance,   2, ".", ",") ."</td>";
        echo "</tr>";

        @$totPayment   = $totPayment + $payment;
        @$totInterest  = $totInterest + $interest;
        @$totPrincipal = $totPrincipal + $principal;

        if ($payment < $interest) {
            echo "</table>";
            echo "<p>Payment < Interest amount - rate is too high, or payment is too low</p>";
            exit;
        } // if

    } while ($balance > 0);

    echo "<tr>";
    echo "<td>&nbsp;</td>";
    echo "<td><b>" .number_format($totPayment,   2, ".", ",") ."</b></td>";
    echo "<td><b>" .number_format($totInterest,  2, ".", ",") ."</b></td>";
    echo "<td><b>" .number_format($totPrincipal, 2, ".", ",") ."</b></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td colspan='5' align='right'><b>Total a receber = R$ ".number_format($totPrincipal - (($totPrincipal / 100) * 4.39), 2, ".", ",")."</b></td>";
    echo "</tr>";

    echo "</table>";

}

function Internet($balance, $rate, $payment)
{

    echo '<table border="1" class="table">';
    echo '<td colspan="5">Venda Parcelada pela Internet 14 dias<br> (Juros '.$rate.'% / Mes)<br>Minimo R$5,00 por parcela.</td>';
    echo '<colgroup align="right" width="20">';
    echo '<colgroup align="right" width="115">';
    echo '<colgroup align="right" width="115">';
    echo '<colgroup align="right" width="115">';
    echo '<colgroup align="right" width="115">';
    echo '<tr><th>#</th><th>PARCELA</th><th>JUROS</th><th>AMORTIZADO</th><th>SALDO</th></tr>';

    $count = 0;
    do {
        $count++;

        // calculate interest on outstanding balance
        $interest = $balance * $rate/100;

        // what portion of payment applies to principal?
        $principal = $payment - $interest;

        // watch out for balance < payment
        if ($balance < $payment) {
            $principal = $balance;
            $payment   = $interest + $principal;
        } // if

        // reduce balance by principal paid
        $balance = $balance - $principal;

        // watch for rounding error that leaves a tiny balance
        if ($balance < 0) {
            $principal = $principal + $balance;
            $interest  = $interest - $balance;
            $balance   = 0;
        } // if

        echo "<tr>";
        echo "<td>$count</td>";
        echo "<td>" .number_format($payment,   2, ".", ",") ."</td>";
        echo "<td>" .number_format($interest,  2, ".", ",") ."</td>";
        echo "<td>" .number_format($principal, 2, ".", ",") ."</td>";
        echo "<td>" .number_format($balance,   2, ".", ",") ."</td>";
        echo "</tr>";

        @$totPayment   = $totPayment + $payment;
        @$totInterest  = $totInterest + $interest;
        @$totPrincipal = $totPrincipal + $principal;

        if ($payment < $interest) {
            echo "</table>";
            echo "<p>Payment < Interest amount - rate is too high, or payment is too low</p>";
            exit;
        } // if

    } while ($balance > 0);

    echo "<tr>";
    echo "<td>&nbsp;</td>";
    echo "<td><b>" .number_format($totPayment,   2, ".", ",") ."</b></td>";
    echo "<td><b>" .number_format($totInterest,  2, ".", ",") ."</b></td>";
    echo "<td><b>" .number_format($totPrincipal, 2, ".", ",") ."</b></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td colspan='5' align='right'><b>Total a receber = R$ ".number_format($totPrincipal - (($totPrincipal / 100) * 4.99), 2, ".", ",")."</b></td>";
    echo "</tr>";

    echo "</table>";

}
?>

<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>