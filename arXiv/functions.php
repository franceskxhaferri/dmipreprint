<?php

#funzione per la verifica se ci sono sessioni attive

function sessioneavviata() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
#importazione variabili globali
    $var = True;
    $a = date("Ymd", time());
    $datas = datasessione();
    $sql = "SELECT attivo FROM sessione";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $row = mysqli_fetch_array($result);
    if (($row['attivo'] == 0) or ( $datas < $a - 1)) {
        $var = False;
    }
    mysqli_close($db_connection);
    return $var;
}

#funzione di avvio della sessione

function avviasessione() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $a = date("Ymd", time());
    $sql = "UPDATE sessione SET attivo='1'";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $sql = "UPDATE sessione_data SET data='" . $a . "'";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    mysqli_close($db_connection);
}

#funzione per terminare la sessione

function chiudisessione() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $sql = "UPDATE sessione SET attivo='0'";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    mysqli_close($db_connection);
}

#funzione verifica nuovo nome

function nomiprec($nome) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    #cerca se il nome se era stato gia cercato...
    $nome = trim($nome);
    $sql = "SELECT * FROM AUTORI_BACKUP WHERE nome='" . $nome . "'";
    $query = mysqli_query($db_connection, $sql) or die(mysql_error());
    $array = mysqli_fetch_row($query);
    if ($array[0] == $nome) {
        mysqli_close($db_connection);
        return True;
    } else {
        mysqli_close($db_connection);
        return False;
    }
}

#funzione ricerca full text

function searchfulltext() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    require_once './authorization/sec_sess.php';
    sec_session_start();
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] < 86400)) {
        if ($_SESSION['logged_type'] === "mod") {
            $cred = 1;
        } else {
            $cred = 0;
        }
    }
    echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    #risultati visualizzati per pagina
    if (isset($_GET['rp']) && $_GET['rp'] != "") {
        $risperpag = $_GET['rp'];
    } else {
        $risperpag = 5;
    }
    if ($_GET['st'] == "1") {
        $query = "SELECT *, MATCH (id_pubblicazione, titolo, data_pubblicazione, autori, referenze, commenti, categoria, abstract) AGAINST('*" . addslashes($_GET['ft']) . "*' IN BOOLEAN MODE) AS attinenza FROM PREPRINTS WHERE MATCH (id_pubblicazione, titolo, data_pubblicazione, autori, referenze, commenti, categoria, abstract) AGAINST ('*" . addslashes($_GET['ft']) . "*' IN BOOLEAN MODE) ORDER BY attinenza DESC";
        $cat = "on currents";
    } else {
        $query = "SELECT *, MATCH (id_pubblicazione, titolo, data_pubblicazione, autori, referenze, commenti, categoria, abstract) AGAINST('*" . addslashes($_GET['ft']) . "*' IN BOOLEAN MODE) AS attinenza FROM PREPRINTS_ARCHIVIATI WHERE MATCH (id_pubblicazione, titolo, data_pubblicazione, autori, referenze, commenti, categoria, abstract) AGAINST ('*" . addslashes($_GET['ft']) . "*' IN BOOLEAN MODE) ORDER BY attinenza DESC";
        $cat = "on archived";
    }
    #recupero pagina
    if (isset($_GET['p']) && $_GET['p'] != "") {
        $p = $_GET['p'];
    } else {
        $p = 1;
    }
    #limite risultati
    $limit = $risperpag * $p - $risperpag;
    #query di ricerca
    $querytotale = mysqli_query($db_connection, $query) or die(mysql_error());
    $ristot = mysqli_num_rows($querytotale);
    if ($ristot != 0) {
        echo "FULLTEXT SEARCH '" . $_GET['ft'] . "' FOUND " . $ristot . " RESULTS(" . $cat . ")(results ordered by pertinence)(results for page " . $_GET['rp'] . ")";
    } else {
        echo "SEARCHED '" . ($_GET['ft']) . "' NOT FOUND!";
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
        break;
    }
    $npag = ceil($ristot / $risperpag);
    $query = $query . " LIMIT " . $limit . "," . $risperpag . "";
    $result = mysqli_query($db_connection, $query) or die(mysql_error());
    echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    #impostazione della paginazione dei risultati
    if ($ristot != 0) {
        if ($p != 1) {
            $t1 = $p - 1;
            $t2 = $p - 2;
            $t3 = $p - 3;
            echo '<a style="color:#007897; text-decoration: none;" title="First page" href="view_preprints.php?p=1&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> &#8656 </a>';
            if ($p >= 3 && $t3 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t3 . " " . ' </a>';
            }
            if ($p >= 2 && $t2 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t2 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t1 . " " . ' </a>';
        }
        echo " " . $p . " ";
        if ($p != $npag) {
            $t4 = $p + 1;
            $t5 = $p + 2;
            $t6 = $p + 3;
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t4 . " " . ' </a>';
            if ($p < $npag && $t5 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t5 . " " . ' </a>';
            }
            if ($p < $npag && $t6 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t6 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="view_preprints.php?p=' . $npag . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> &#8658 </a>';
        }
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    $i = $limit;
    #recupero e visualizzazione dei campi della ricerca effettuata
    while ($row = mysqli_fetch_array($result)) {
        $i++;
        if ($cred == 1) {
            echo "<h1>" . $i . ".<br/></h1><div align='left' style='width:98%;'>";
            if ($_COOKIE['pageview'] == "0") {
                echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a>&nbsp&nbsp&nbsp<a title='Change this preprint' style='color:#007897;' href='./manual_edit.php?id=" . $row['id_pubblicazione'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
            } else {
                echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a title='Change this preprint' style='color:#007897;' href='./manual_edit.php?id=" . $row['id_pubblicazione'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
            }
        } else {
            echo "<h1>" . $i . ".<br/></h1><div align='left' style='width:98%;'>";
            if ($_COOKIE['pageview'] == "0") {
                if ($_SESSION['nome'] . " (" . $_SESSION['uid'] . ")" == $row['uid'] && $row['uid'] != "") {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a>&nbsp&nbsp&nbsp<a title='Change this preprint' style='color:#007897;' href='./edit.php?id=" . $row['id_pubblicazione'] . "&r=" . $row['uid'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                } else {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                }
            } else {
                if ($_SESSION['nome'] . " (" . $_SESSION['uid'] . ")" == $row['uid'] && $row['uid'] != "") {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a title='Change this preprint' style='color:#007897;' href='./edit.php?id=" . $row['id_pubblicazione'] . "&r=" . $row['uid'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                } else {
                    echo "<p><h1>Id of publication:</h1></p><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                }
            }
        }
        echo "<p><h1>Title:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['titolo']) . "</div>";
        echo "<p><h1>Date of publication:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['data_pubblicazione']) . "</div>";
        echo "<p><h1>Authors:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['autori']) . "</div>";
        echo "<p><h1>Journal reference:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['referenze']) . "</div>";
        echo "<p><h1>Comments:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['commenti']) . "</div>";
        echo "<p><h1>Category:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['categoria']) . "</div>";
        echo "<p><h1>Abstract:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['abstract']) . "</div>";
        $na = $row['Filename'];
        $na = substr($na, -3, 3);
        #controllo se il file é un pdf
        if ($na == "pdf") {
            if ($_COOKIE['pageview'] == "1") {
                #visualizzazione integrata del pdf
                echo "<p><h1>pdf:</h1></p><center><embed style='display: block; border: 1px; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;' width='850px' height='600px' src='" . $copia . $row['Filename'] . "'></center>";
            }
        } else {
            if ($_COOKIE['pageview'] == "1") {
                echo "<p><h1>document:</h1></p><div style='margin-left:1%; margin-right:1%;'><a style='color:#007897;' href=" . $copia . $row['Filename'] . " onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>LINK</a> (On page view disabled for this file)</div>";
            }
        }
        echo "<p><h1>Views:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['counter']) . "</div>";
        echo "</div><hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    #impostazioni della navigazione per pagine
    if ($ristot != 0) {
        if ($p != 1) {
            $t1 = $p - 1;
            $t2 = $p - 2;
            $t3 = $p - 3;
            echo '<a style="color:#007897; text-decoration: none;" title="First page" href="view_preprints.php?p=1&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> &#8656 </a>';
            if ($p >= 3 && $t3 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t3 . " " . ' </a>';
            }
            if ($p >= 2 && $t2 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t2 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t1 . " " . ' </a>';
        }
        echo " " . $p . " ";
        if ($p != $npag) {
            $t4 = $p + 1;
            $t5 = $p + 2;
            $t6 = $p + 3;
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t4 . " " . ' </a>';
            if ($p < $npag && $t5 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t5 . " " . ' </a>';
            }
            if ($p < $npag && $t6 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> ' . " " . $t6 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="view_preprints.php?p=' . $npag . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '&ft=' . $_GET['ft'] . '&go=' . $_GET['go'] . '&st=' . $_GET['st'] . '"> &#8658 </a>';
        }
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    $x = $limit + 1;
    echo "RESULTS FROM " . $x . " TO " . ($p * $risperpag) . "<br/><br/>";
    mysqli_close($db_connection);
}

# funzione lettura dei preprint

function searchpreprint() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    require_once './authorization/sec_sess.php';
    sec_session_start();
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] < 86400)) {
        if ($_SESSION['logged_type'] === "mod") {
            $cred = 1;
        } else {
            $cred = 0;
        }
    }
    #verifica ordine risultati
    if ($_GET['o'] == "dated") {
        $order = "data_pubblicazione DESC";
        $orstr = "decreasing date";
    } else if ($_GET['o'] == "datec") {
        $order = "data_pubblicazione ASC";
        $orstr = "increasing date";
    } else if ($_GET['o'] == "named") {
        $order = "autori DESC";
        $orstr = "decreasing name";
    } else if ($_GET['o'] == "namec") {
        $order = "autori ASC";
        $orstr = "increasing name";
    } else if ($_GET['o'] == "idd") {
        $order = "id_pubblicazione DESC";
        $orstr = "decreasing ID";
    } else {
        $order = "id_pubblicazione ASC";
        $orstr = "increasing ID";
    }
    # controllo ricerca per anno
    if (isset($_GET['year1']) && is_numeric($_GET['year1'])) {
        if ($_GET['d'] != "1") {
            $query = " SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND data_pubblicazione <= '" . addslashes($_GET['year1'] + 1) . "' AND checked='1' UNION ";
            $cat3 = "until year " . $_GET['year1'] . ", ";
        } else {
            $query = " SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND data_pubblicazione <= '" . addslashes($_GET['year1'] + 1) . "' AND checked='1' UNION 
    		SELECT * FROM PREPRINTS_ARCHIVIATI WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND data_pubblicazione <= '" . addslashes($_GET['year1'] + 1) . "' AND checked='1' UNION ";
            $cat3 = "until year " . $_GET['year1'] . " with archived, ";
        }
    } else if (isset($_GET['year2']) && is_numeric($_GET['year2']) && isset($_GET['year3']) && is_numeric($_GET['year3'])) {
        if ($_GET['d'] != "1") {
            $query = " SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND data_pubblicazione BETWEEN '" . addslashes($_GET['year2']) . "' AND '" . addslashes($_GET['year3'] + 1) . "' AND checked='1' UNION ";
            $cat3 = "on range from " . $_GET['year2'] . " to " . $_GET['year3'] . ", ";
        } else {
            $query = " SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND data_pubblicazione BETWEEN '" . addslashes($_GET['year2']) . "' AND  '" . addslashes($_GET['year3'] + 1) . "' AND checked='1' UNION SELECT * FROM PREPRINTS_ARCHIVIATI WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND data_pubblicazione BETWEEN '" . addslashes($_GET['year2']) . "' AND '" . addslashes($_GET['year3'] + 1) . "' AND checked='1' UNION ";
            $cat3 = "on range from " . $_GET['year2'] . " to " . $_GET['year3'] . " with archived, ";
        }
    } else {
        $cat = 0;
        #verifica parametri ricerca
        if ($_GET['e'] != 1 && $_GET['i'] != 1 && $_GET['t'] != 1 && $_GET['a'] != 1 && $_GET['c'] != 1 && $_GET['j'] != 1 && $_GET['h'] != 1 && $_GET['y'] != 1 && $_GET['all'] != 1 && $_GET['d'] == 1) {
            $query = " 
	    	SELECT * FROM PREPRINTS WHERE id_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE titolo LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE referenze LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE commenti LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE categoria LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE abstract LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION 
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE id_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE titolo LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE referenze LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE commenti LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE categoria LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE abstract LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
            $cat = "ALL";
        }
        if ($_GET['all'] != "1") {
            if ($_GET['d'] != "1") {
                if ($_GET['h'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "authors, ";
                }
                if ($_GET['t'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE titolo LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "title, ";
                }
                if ($_GET['a'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE abstract LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "abstract, ";
                }
                if ($_GET['y'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE categoria LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "category, ";
                }
                if ($_GET['c'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE commenti LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "comments, ";
                }
                if ($_GET['j'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE referenze LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "journal-ref, ";
                }
                if ($_GET['e'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "year, ";
                }
                if ($_GET['i'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE id_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "ID, ";
                }
            } else {
                if ($_GET['h'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION 
		    SELECT * FROM PREPRINTS_ARCHIVIATI WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "authors, ";
                }
                if ($_GET['t'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE titolo LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION 
		    SELECT * FROM PREPRINTS_ARCHIVIATI WHERE titolo LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "title, ";
                }
                if ($_GET['a'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE abstract LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION SELECT * FROM PREPRINTS_ARCHIVIATI WHERE abstract LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "abstract, ";
                }
                if ($_GET['y'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE categoria LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION SELECT * FROM PREPRINTS_ARCHIVIATI WHERE categoria LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "category, ";
                }
                if ($_GET['c'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE commenti LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION SELECT * FROM PREPRINTS_ARCHIVIATI WHERE commenti LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "comments, ";
                }
                if ($_GET['j'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE referenze LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION SELECT * FROM PREPRINTS_ARCHIVIATI WHERE referenze LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "journal-ref, ";
                }
                if ($_GET['e'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION SELECT * FROM PREPRINTS_ARCHIVIATI WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "year, ";
                }
                if ($_GET['i'] == "1") {
                    $query = $query . "SELECT * FROM PREPRINTS WHERE id_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION SELECT * FROM PREPRINTS_ARCHIVIATI WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                    $cat++;
                    $cat3 = $cat3 . "ID, ";
                }
                $cat3 = $cat3 . "included archived, ";
            }
        } else {
            if ($_GET['d'] != "1") {
                $query = " 
	    	SELECT * FROM PREPRINTS WHERE id_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE titolo LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE referenze LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE commenti LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE categoria LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE abstract LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                $cat = "all records";
            } else {
                $query = " 
	    	SELECT * FROM PREPRINTS WHERE id_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE titolo LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE referenze LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE commenti LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE categoria LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS WHERE abstract LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION 
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE id_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE titolo LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE referenze LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE commenti LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE categoria LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
	    	SELECT * FROM PREPRINTS_ARCHIVIATI WHERE abstract LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION ";
                $cat = "all records";
                $cat3 = $cat3 . ", included archived, ";
            }
        }
    }
    $query = substr($query, 0, -7);
    $cat3 = substr($cat3, 0, -2);
    echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    #risultati visualizzati per pagina
    if (isset($_GET['rp']) && $_GET['rp'] != "") {
        $risperpag = $_GET['rp'];
    } else {
        $risperpag = 5;
    }
    #recupero pagina
    if (isset($_GET['p']) && $_GET['p'] != "") {
        $p = $_GET['p'];
    } else {
        $p = 1;
    }
    #limite risultati
    $limit = $risperpag * $p - $risperpag;
    #query di ricerca
    $querytotale = mysqli_query($db_connection, $query) or die(mysql_error());
    $ristot = mysqli_num_rows($querytotale);
    if ($cat != "all records") {
        echo "SEARCH '" . $_GET['r'] . "' FOUND " . $ristot . " RESULTS(" . $cat3 . ")(results ordered by " . $orstr . ")(results for page " . $_GET['rp'] . ")";
    } else {
        echo "SEARCH '" . $_GET['r'] . "' FOUND " . $ristot . " RESULTS(" . $cat . $cat3 . ")(results ordered by " . $orstr . ")(results for page " . $_GET['rp'] . ")";
    }
    $npag = ceil($ristot / $risperpag);
    $query = $query . " ORDER BY " . $order . " LIMIT " . $limit . "," . $risperpag . "";
    $result = mysqli_query($db_connection, $query) or die(mysql_error());
    echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    #impostazione della paginazione dei risultati
    if ($ristot != 0) {
        if ($p != 1) {
            $t1 = $p - 1;
            $t2 = $p - 2;
            $t3 = $p - 3;
            echo '<a style="color:#007897; text-decoration: none;" title="First page" href="view_preprints.php?p=1&w=&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> &#8656 </a>';
            if ($p >= 3 && $t3 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t3 . " " . ' </a>';
            }
            if ($p >= 2 && $t2 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t2 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t1 . " " . ' </a>';
        }
        echo " " . $p . " ";
        if ($p != $npag) {
            $t4 = $p + 1;
            $t5 = $p + 2;
            $t6 = $p + 3;
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t4 . " " . ' </a>';
            if ($p < $npag && $t5 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t5 . " " . ' </a>';
            }
            if ($p < $npag && $t6 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t6 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="view_preprints.php?p=' . $npag . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> &#8658 </a>';
        }
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    $i = $limit;
    #recupero e visualizzazione dei campi della ricerca effettuata
    while ($row = mysqli_fetch_array($result)) {
        $i++;
        if ($cred == 1) {
            echo "<h1>" . $i . ".<br/></h1><div align='left' style='width:98%;'>";
            if ($_COOKIE['pageview'] == "0") {
                if (file_exists($copia . $row['Filename'])) {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a>&nbsp&nbsp&nbsp<a title='Change this preprint' style='color:#007897;' href='./manual_edit.php?id=" . $row['id_pubblicazione'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                } else {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href='" . $basedir4 . $row['Filename'] . "' onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a> (Archived)</div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                }
            } else {
                if (file_exists($copia . $row['Filename'])) {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a title='Change this preprint' style='color:#007897;' href='./manual_edit.php?id=" . $row['id_pubblicazione'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                } else {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'>(Archived)</div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                }
            }
        } else {
            echo "<h1>" . $i . ".<br/></h1><div align='left' style='width:98%;'>";
            #visualizzazione
            if ($_COOKIE['pageview'] == "0") {
                if ($_SESSION['nome'] . " (" . $_SESSION['uid'] . ")" == $row['uid'] && $row['uid'] != "") {
                    if (file_exists($copia . $row['Filename'])) {
                        echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a>&nbsp&nbsp&nbsp<a title='Change this preprint' style='color:#007897;' href='./edit.php?id=" . $row['id_pubblicazione'] . "&r=" . $row['uid'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                    } else {
                        echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=" . $basedir4 . $row['Filename'] . " onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a> (Archived)</div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                    }
                } else {
                    if (file_exists($copia . $row['Filename'])) {
                        echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                    } else {
                        echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href='" . $basedir4 . $row['Filename'] . "' onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a> (Archived)</div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                    }
                }
            } else {
                if ($_SESSION['nome'] . " (" . $_SESSION['uid'] . ")" == $row['uid'] && $row['uid'] != "") {
                    if (file_exists($copia . $row['Filename'])) {
                        echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a title='Change this preprint' style='color:#007897;' href='./edit.php?id=" . $row['id_pubblicazione'] . "&r=" . $row['uid'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                    } else {
                        echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a title='Change this preprint' style='color:#007897;' href='" . $basedir4 . $row['Filename'] . "' onclick='window.open(this.href); return false'>view</a> (Archived)</div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                    }
                } else {
                    if (file_exists($copia . $row['Filename'])) {
                        echo "<p><h1>Id of publication:</h1></p><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                    } else {
                        echo "<p><h1>Id of publication:</h1></p><div style='float:right;'>(Archived)</div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                    }
                }
            }
        }
        echo "<p><h1>Title:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['titolo']) . "</div>";
        echo "<p><h1>Date of publication:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['data_pubblicazione']) . "</div>";
        echo "<p><h1>Authors:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['autori']) . "</div>";
        echo "<p><h1>Journal reference:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['referenze']) . "</div>";
        echo "<p><h1>Comments:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['commenti']) . "</div>";
        echo "<p><h1>Category:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['categoria']) . "</div>";
        echo "<p><h1>Abstract:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['abstract']) . "</div>";
        $na = $row['Filename'];
        $na = substr($na, -3, 3);
        #controllo se il file é un pdf
        if ($na == "pdf") {
            if ($_COOKIE['pageview'] == "1") {
                if (file_exists($copia . $row['Filename'])) {
                    #visualizzazione integrata del pdf
                    echo "<p><h1>pdf:</h1></p><center><embed style='display: block; border: 1px; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;' width='850px' height='600px' src='" . $copia . $row['Filename'] . "'></center>";
                } else {
                    echo "<p><h1>pdf:</h1></p><center><embed style='display: block; border: 1px; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;' width='850px' height='600px' src='" . $basedir4 . $row['Filename'] . "'></center>";
                }
            }
        } else {
            if ($_COOKIE['pageview'] == "1") {
                if (file_exists($copia . $row['Filename'])) {
                    echo "<p><h1>document:</h1></p><div style='margin-left:1%; margin-right:1%;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>LINK</a> (On page view disabled for this file)</div>";
                } else {
                    echo "<p><h1>document:</h1></p><div style='margin-left:1%; margin-right:1%;'><a style='color:#007897;' href='" . $basedir4 . $row['Filename'] . "' onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>LINK</a></div>";
                }
            }
        }
        echo "<p><h1>Views:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . number_format(($row['counter']), 0, ',', '.') . "</div>";
        echo "</div><hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    #impostazioni della navigazione per pagine
    if ($ristot != 0) {
        if ($p != 1) {
            $t1 = $p - 1;
            $t2 = $p - 2;
            $t3 = $p - 3;
            echo '<a style="color:#007897; text-decoration: none;" title="First page" href="view_preprints.php?p=1&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> &#8656 </a>';
            if ($p >= 3 && $t3 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t3 . " " . ' </a>';
            }
            if ($p >= 2 && $t2 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t2 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t1 . " " . ' </a>';
        }
        echo " " . $p . " ";
        if ($p != $npag) {
            $t4 = $p + 1;
            $t5 = $p + 2;
            $t6 = $p + 3;
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t4 . " " . ' </a>';
            if ($p < $npag && $t5 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t5 . " " . ' </a>';
            }
            if ($p < $npag && $t6 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> ' . " " . $t6 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="view_preprints.php?p=' . $npag . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&t=' . $_GET['t'] . '&a=' . $_GET['a'] . '&c=' . $_GET['c'] . '&j=' . $_GET['j'] . '&d=' . $_GET['d'] . '&all=' . $_GET['all'] . '&h=' . $_GET['h'] . '&y=' . $_GET['y'] . '&e=' . $_GET['e'] . '&i=' . $_GET['i'] . '&rp=' . $_GET['rp'] . '&year1=' . $_GET['year1'] . '&year2=' . $_GET['year2'] . '&year3=' . $_GET['year3'] . '"> &#8658 </a>';
        }
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    $x = $limit + 1;
    echo "RESULTS FROM " . $x . " TO " . ($p * $risperpag) . "<br/><br/>";
    mysqli_close($db_connection);
}

# funzione filtro e lettura dei preprint

function filtropreprint() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    require_once './authorization/sec_sess.php';
    sec_session_start();
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] < 86400)) {
        if ($_SESSION['logged_type'] === "mod") {
            $cred = 1;
        } else {
            $cred = 0;
        }
    }
    #recupero pagina
    if (isset($_GET['p']) && $_GET['p'] != "") {
        $p = $_GET['p'];
    } else {
        $p = 1;
    }
    #verifica ordine risultati
    if ($_GET['o'] == "dated") {
        $order = "data_pubblicazione DESC";
        $orstr = "decreasing date";
    } else if ($_GET['o'] == "datec") {
        $order = "data_pubblicazione ASC";
        $orstr = "increasing date";
    } else if ($_GET['o'] == "named") {
        $order = "autori DESC";
        $orstr = "decreasing name";
    } else if ($_GET['o'] == "namec") {
        $order = "autori ASC";
        $orstr = "increasing name";
    } else if ($_GET['o'] == "idd") {
        $order = "id_pubblicazione DESC";
        $orstr = "decreasing ID";
    } else {
        $order = "id_pubblicazione ASC";
        $orstr = "increasing ID";
    }
    #verifica filtro
    if ($_GET['f'] == "author") {
        $argom = "autori";
    } else if ($_GET['f'] == "category") {
        $argom = "categoria";
    } else if ($_GET['f'] == "year") {
        $argom = "data_pubblicazione";
    } else if ($_GET['f'] == "id") {
        $argom = "id_pubblicazione";
    }
    echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    #risultati visualizzati per pagina
    if (isset($_GET['rp']) && $_GET['rp'] != "") {
        $risperpag = $_GET['rp'];
    } else {
        $risperpag = 5;
    }
    #limite risultati
    $limit = $risperpag * $p - $risperpag;
    #query di ricerca
    if (isset($argom)) {
        $sql = "SELECT * FROM PREPRINTS WHERE " . $argom . " LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1'";
        $querytotale = mysqli_query($db_connection, $sql) or die(mysql_error());
        $ristot = mysqli_num_rows($querytotale);
        if ($ristot != 0) {
            echo "SEARCH '" . $_GET['r'] . "' FOUND " . $ristot . " RESULTS(" . $_GET['f'] . " filter)(results ordered by " . $orstr . ")(results for page " . $_GET['rp'] . ")";
        } else {
            echo "SEARCHED '" . ($_GET['r']) . "' NOT FOUND!(" . $_GET['f'] . " filter)";
            echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
            break;
        }
        $npag = ceil($ristot / $risperpag);
        $sql = "SELECT * FROM PREPRINTS WHERE " . $argom . " LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' ORDER BY " . $order . " LIMIT " . $limit . "," . $risperpag . "";
        $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    } else {
        #senza filtro
        $query = " 
    	SELECT * FROM PREPRINTS WHERE id_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
    	SELECT * FROM PREPRINTS WHERE titolo LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
    	SELECT * FROM PREPRINTS WHERE data_pubblicazione LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
    	SELECT * FROM PREPRINTS WHERE autori LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
    	SELECT * FROM PREPRINTS WHERE referenze LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
    	SELECT * FROM PREPRINTS WHERE commenti LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
    	SELECT * FROM PREPRINTS WHERE categoria LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1' UNION
    	SELECT * FROM PREPRINTS WHERE abstract LIKE '%" . addslashes($_GET['r']) . "%' AND checked='1'";
        $querytotale = mysqli_query($db_connection, $query) or die(mysql_error());
        $ristot = mysqli_num_rows($querytotale);
        $npag = ceil($ristot / $risperpag);
        if (isset($_GET['o']) && $_GET['o'] != "") {
            $query = $query . " ORDER BY " . $order . " LIMIT " . $limit . "," . $risperpag . "";
        } else {
            $query = $query . " ORDER BY data_pubblicazione DESC LIMIT " . $limit . "," . $risperpag . "";
        }
        if (!isset($_GET['r']) or $_GET['r'] == "") {
            echo $ristot . " ELEMENTS ON " . $npag . " PAGES";
        } else {
            echo "SEARCH '" . $_GET['r'] . "' FOUND " . $ristot . " RESULTS(" . $_GET['f'] . ")(results ordered by " . $orstr . ")(results for page " . $_GET['rp'] . ")";
        }
        $npag = ceil($ristot / $risperpag);
        $result = mysqli_query($db_connection, $query) or die(mysql_error());
    }
    echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    #impostazione della paginazione dei risultati
    if ($ristot != 0) {
        if ($p != 1) {
            $t1 = $p - 1;
            $t2 = $p - 2;
            $t3 = $p - 3;
            echo '<a style="color:#007897; text-decoration: none;" title="First page" href="view_preprints.php?p=1&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> &#8656 </a>';
            if ($p >= 3 && $t3 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t3 . " " . ' </a>';
            }
            if ($p >= 2 && $t2 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t2 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t1 . " " . ' </a>';
        }
        echo " " . $p . " ";
        if ($p != $npag) {
            $t4 = $p + 1;
            $t5 = $p + 2;
            $t6 = $p + 3;
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t4 . " " . ' </a>';
            if ($p < $npag && $t5 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t5 . " " . ' </a>';
            }
            if ($p < $npag && $t6 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t6 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="view_preprints.php?p=' . $npag . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> &#8658 </a>';
        }
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    $i = $limit;
    #recupero e visualizzazione dei campi della ricerca effettuata
    while ($row = mysqli_fetch_array($result)) {
        $i++;
        if ($cred == 1) {
            echo "<h1>" . $i . ".<br/></h1><div align='left' style='width:98%;'>";
            if ($_COOKIE['pageview'] == "0") {
                echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a>&nbsp&nbsp&nbsp<a title='Change this preprint' style='color:#007897;' href='./manual_edit.php?id=" . $row['id_pubblicazione'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
            } else {
                echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a title='Change this preprint' style='color:#007897;' href='./manual_edit.php?id=" . $row['id_pubblicazione'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
            }
        } else {
            echo "<h1>" . $i . ".<br/></h1><div align='left' style='width:98%;'>";
            if ($_COOKIE['pageview'] == "0") {
                if ($_SESSION['nome'] . " (" . $_SESSION['uid'] . ")" == $row['uid'] && $row['uid'] != "") {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a>&nbsp&nbsp&nbsp<a title='Change this preprint' style='color:#007897;' href='./edit.php?id=" . $row['id_pubblicazione'] . "&r=" . $row['uid'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                } else {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href=./mysql/counter.php?id=" . $row['id_pubblicazione'] . "&i=1 onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                }
            } else {
                if ($_SESSION['nome'] . " (" . $_SESSION['uid'] . ")" == $row['uid'] && $row['uid'] != "") {
                    echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a title='Change this preprint' style='color:#007897;' href='./edit.php?id=" . $row['id_pubblicazione'] . "&r=" . $row['uid'] . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                } else {
                    echo "<p><h1>Id of publication:</h1></p><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
                }
            }
        }
        echo "<p><h1>Title:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['titolo']) . "</div>";
        echo "<p><h1>Date of publication:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['data_pubblicazione']) . "</div>";
        echo "<p><h1>Authors:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['autori']) . "</div>";
        echo "<p><h1>Journal reference:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['referenze']) . "</div>";
        echo "<p><h1>Comments:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['commenti']) . "</div>";
        echo "<p><h1>Category:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['categoria']) . "</div>";
        echo "<p><h1>Abstract:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['abstract']) . "</div>";
        $na = $row['Filename'];
        $na = substr($na, -3, 3);
        #controllo se il file é un pdf
        if ($na == "pdf") {
            if ($_COOKIE['pageview'] == "1") {
                #visualizzazione integrata del pdf
                echo "<p><h1>pdf:</h1></p><center><embed style='display: block; border: 1px; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;' width='850px' height='600px' src='" . $copia . $row['Filename'] . "'></center>";
            }
        } else {
            if ($_COOKIE['pageview'] == "1") {
                echo "<p><h1>document:</h1></p><div style='margin-left:1%; margin-right:1%;'><a style='color:#007897;' href=" . $copia . $row['Filename'] . " onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>LINK</a> (On page view disabled for this file)</div>";
            }
        }
        echo "<p><h1>Views:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . number_format(($row['counter']), 0, ',', '.') . "</div>";
        echo "</div><hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    #impostazioni della navigazione per pagine
    if ($ristot != 0) {
        if ($p != 1) {
            $t1 = $p - 1;
            $t2 = $p - 2;
            $t3 = $p - 3;
            echo '<a style="color:#007897; text-decoration: none;" title="First page" href="view_preprints.php?p=1&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> &#8656 </a>';
            if ($p >= 3 && $t3 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t3 . " " . ' </a>';
            }
            if ($p >= 2 && $t2 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t2 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p - 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t1 . " " . ' </a>';
        }
        echo " " . $p . " ";
        if ($p != $npag) {
            $t4 = $p + 1;
            $t5 = $p + 2;
            $t6 = $p + 3;
            echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 1) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t4 . " " . ' </a>';
            if ($p < $npag && $t5 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 2) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t5 . " " . ' </a>';
            }
            if ($p < $npag && $t6 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="view_preprints.php?p=' . ($p + 3) . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> ' . " " . $t6 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="view_preprints.php?p=' . $npag . '&r=' . $_GET['r'] . '&f=' . $_GET['f'] . '&o=' . $_GET['o'] . '&rp=' . $_GET['rp'] . '"> &#8658 </a>';
        }
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    $x = $limit + 1;
    echo "RESULTS FROM " . $x . " TO " . ($p * $risperpag) . "<br/><br/>";
    mysqli_close($db_connection);
}

# funzione lettura dei preprint recenti

function recentspreprints() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    require_once './authorization/sec_sess.php';
    sec_session_start();
    $query="SELECT * FROM PREPRINTS ORDER BY data_pubblicazione DESC LIMIT 10";
        $result = mysqli_query($db_connection, $query) or die(mysql_error());
    $i = $limit;
    #recupero e visualizzazione dei campi della ricerca effettuata
    while ($row = mysqli_fetch_array($result)) {
    	echo '<div class="boxContainer" align="center">';
    	echo "<div style='clear:both;'>";
        echo "<a style='color:#1976D2; font-weight:bold;' href='" . $copia . $row['Filename'] . "' onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>" . ($row['titolo']) . "</a>";
        echo "<div style='margin-left:1%; margin-right:1%;'>" . ($row['data_pubblicazione']) . "</div>";
        echo "<div style='margin-left:1%; margin-right:1%;'>" . ($row['autori']) . "</div>";
        $na = $row['Filename'];
        $na = substr($na, -3, 3);
        echo "</div></div></div>";
    }
    $x = $limit + 1;
    mysqli_close($db_connection);
}

#funzione lettura dei preprint archiviati

function leggipreprintarchiviati() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $risperpag = 5;
    #recupero pagina
    if (isset($_GET['p']) && $_GET['p'] != "") {
        $p = $_GET['p'];
    } else {
        $p = 1;
    }
    $limit = $risperpag * $p - $risperpag;
    $sql = "SELECT * FROM PREPRINTS_ARCHIVIATI";
    $querytotale = mysqli_query($db_connection, $sql) or die(mysql_error());
    $ristot = mysqli_num_rows($querytotale);
    echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    echo "ELEMENTS ARCHIVED: " . $ristot . "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    $npag = ceil($ristot / $risperpag);
    #impostazione della navigazione per pagine
    if ($ristot != 0) {
        if ($p != 1) {
            $t1 = $p - 1;
            $t2 = $p - 2;
            $t3 = $p - 3;
            echo '<a style="color:#007897; text-decoration: none;" title="First page" href="archived_preprints.php?p=1&r=' . $_GET['r'] . '"> &#8656 </a>';
            if ($p >= 3 && $t3 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p - 3) . '&r=' . $_GET['r'] . '"> ' . " " . $t3 . " " . ' </a>';
            }
            if ($p >= 2 && $t2 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p - 2) . '&r=' . $_GET['r'] . '"> ' . " " . $t2 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p - 1) . '&r=' . $_GET['r'] . '"> ' . " " . $t1 . " " . ' </a>';
        }
        echo " " . $p . " ";
        if ($p != $npag) {
            $t4 = $p + 1;
            $t5 = $p + 2;
            $t6 = $p + 3;
            echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p + 1) . '&r=' . $_GET['r'] . '"> ' . " " . $t4 . " " . ' </a>';
            if ($p < $npag && $t5 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p + 2) . '&r=' . $_GET['r'] . '"> ' . " " . $t5 . " " . ' </a>';
            }
            if ($p < $npag && $t6 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p + 3) . '&r=' . $_GET['r'] . '"> ' . " " . $t6 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="archived_preprints.php?p=' . $npag . '&r=' . $_GET['r'] . '"> &#8658 </a>';
        }
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    #verifica se i preprint devono essere rimossi definitivamente
    if ($_GET['c'] != "Remove") {
        $sql = "SELECT * FROM PREPRINTS_ARCHIVIATI WHERE checked='1' ORDER BY data_pubblicazione DESC LIMIT " . $limit . "," . $risperpag . "";
        $result = mysqli_query($db_connection, $sql) or die(mysql_error());
        $i = $limit;
        #recupero info e visualizzazione
        while ($row = mysqli_fetch_array($result)) {
            $i++;
            echo "<h1>" . $i . ".<br/></h1><div align='left' style='width:98%;'>";
            echo "<p><h1>Id of publication:</h1></p><div style='float:right;'><a style='color:#007897;' href='" . $basedir4 . $row['Filename'] . "' onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
            echo "<p><h1>Title:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['titolo']) . "</div>";
            echo "<p><h1>Date of pubblication:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['data_pubblicazione']) . "</div>";
            echo "<p><h1>Authors:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['autori']) . "</div>";
            echo "<p><h1>Journal reference:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['referenze']) . "</div>";
            echo "<p><h1>Comments:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['commenti']) . "</div>";
            echo "<p><h1>Category:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['categoria']) . "</div>";
            echo "<p><h1>Abstract:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . ($row['abstract']) . "</div>";
            echo "<p><h1>Views:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . number_format(($row['counter']), 0, ',', '.') . "</div>";
            echo "</div><hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
        }
        #visualizzazione della navigazione per pagine
        if ($ristot != 0) {
            if ($p != 1) {
                echo '<a style="color:#007897; text-decoration: none;" title="First page" href="archived_preprints.php?p=1&r=' . $_GET['r'] . '"> &#8656 </a>';
                if ($p >= 3 && $t3 > 0) {
                    echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p - 3) . '&r=' . $_GET['r'] . '"> ' . " " . $t3 . " " . ' </a>';
                }
                if ($p >= 2 && $t2 > 0) {
                    echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p - 2) . '&r=' . $_GET['r'] . '"> ' . " " . $t2 . " " . ' </a>';
                }
                echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p - 1) . '&r=' . $_GET['r'] . '"> ' . " " . $t1 . " " . ' </a>';
            }
            echo " " . $p . " ";
            if ($p != $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p + 1) . '&r=' . $_GET['r'] . '"> ' . " " . $t4 . " " . ' </a>';
                if ($p < $npag && $t5 <= $npag) {
                    echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p + 2) . '&r=' . $_GET['r'] . '"> ' . " " . $t5 . " " . ' </a>';
                }
                if ($p < $npag && $t6 <= $npag) {
                    echo '<a style="color:#007897; text-decoration: none;" href="archived_preprints.php?p=' . ($p + 3) . '&r=' . $_GET['r'] . '"> ' . " " . $t6 . " " . ' </a>';
                }
                echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="archived_preprints.php?p=' . $npag . '&r=' . $_GET['r'] . '"> &#8658 </a>';
            }
            echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
        }
    } else {
        #controllo di preprint da rimuovere
        if ($ristot != 0) {
            cancellapreprint();
            echo '<script type="text/javascript">alert("Papers deleted from database!");</script>';
            echo '<META HTTP-EQUIV="Refresh" Content="0; URL=./archived_preprints.php?p=1">';
        } else {
            $limit = 0;
            echo '<script type="text/javascript">alert("No archived papers!");</script>';
        }
    }
    $x = $limit + 1;
    echo "RESULTS FROM " . $x . " TO " . ($p * 5) . "<br/><br/>";
    mysqli_close($db_connection);
}

# funzione cancellazione preprint

function cancellaselected($id) {
    include './header.inc.php';
    include './mysql/db_conn.php';
    $sql = "DELETE FROM PREPRINTS WHERE id_pubblicazione='" . $id . "'";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    mysqli_close($db_connection);
}

# funzione cancellazione preprint archiviati

function cancellapreprint() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $sql = "SELECT * FROM PREPRINTS_ARCHIVIATI WHERE checked='1'";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    while ($row = mysqli_fetch_array($result)) {
        unlink($basedir4 . $row['Filename']);
    }
    $sql = "TRUNCATE TABLE PREPRINTS_ARCHIVIATI";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    mysqli_close($db_connection);
}

#funzione che controlla se si sono verificate interruzioni nell'ultimo update

function controllainterruzione() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $var = False;
    $sql = "SELECT id FROM temp";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    if ((mysqli_num_rows($result)) != 0) {
        $var = True;
    }
    mysqli_close($db_connection);
    return $var;
}

#funzione che cerca se il preprint è stato già scaricato nell'esecuzione in corso

function preprintscaricati($id) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $var = False;
    $sql = "SELECT id FROM temp";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    while ($row = mysqli_fetch_array($result)) {
        if ($row['id'] == $id) {
            $var = True;
        }
    }
    mysqli_close($db_connection);
    return $var;
}

#funzione per l'inserimento dell'id dentro temp

function aggiornapreprintscaricati($id) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $sql = "INSERT INTO temp (id) VALUES ('" . $id . "') ON DUPLICATE KEY UPDATE id = VALUES(id)";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    mysqli_close($db_connection);
}

#funzione per la cancellazione del contenuto temp

function azzerapreprint() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $sql = "TRUNCATE TABLE temp";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    mysqli_close($db_connection);
}

#funzione che cerca se il preprint se è presente

function cercapreprint($id) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $id = trim($id);
    $sql = "SELECT * FROM PREPRINTS WHERE id_pubblicazione='" . $id . "'";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $row = mysqli_fetch_array($result);
    if ($row['nome'] == $nome) {
        $var[0] = $row['id_pubblicazione'];
        $var[1] = ($row['titolo']);
        $var[2] = ($row['data_pubblicazione']);
        $var[3] = ($row['autori']);
        $var[4] = ($row['referenze']);
        $var[5] = ($row['commenti']);
        $var[6] = ($row['categoria']);
        $var[7] = ($row['abstract']);
        $var[8] = ($row['uid']);
        $var[9] = ($row['Filename']);
    }
    mysqli_close($db_connection);
    return $var;
}

#funzione che cerca se il nome è presente

function cercanome($nome) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    #cerca se il nome se era stato gia cercato...
    $nome = trim($nome);
    $var = False;
    $sql = "SELECT * FROM AUTORI WHERE nome='" . $nome . "'";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $row = mysqli_fetch_array($result);
    if ($row['nome'] == $nome) {
        $var = True;
    }
    mysqli_close($db_connection);
    return $var;
}

#funzione aggiornamento nomi_ultimo_lancio

function aggiornanomi() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    #leggo i nuovi nomi e li inserisco in array...
    $array = legginomi();
    $sql = "TRUNCATE TABLE AUTORI_BACKUP";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $nl2 = count($array);
    #aggiorno i nomi...
    for ($i = 0; $i < $nl2; $i++) {
        $sql = "INSERT INTO AUTORI_BACKUP (nome) VALUES ('" . $array[$i] . "')";
        $query = mysqli_query($db_connection, $sql) or die(mysql_error());
    }
    mysqli_close($db_connection);
}

# funzione lettura nomi

function legginomi() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $sql = "SELECT nome FROM AUTORI";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $i = 0;
    while ($row = mysqli_fetch_array($result)) {
        $array[$i] = $row['nome'];
        $i++;
    }
    mysqli_close($db_connection);
    return $array;
}

#funzione scrittura nomi

function scrivinomi($nomi) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $sql = "TRUNCATE TABLE AUTORI";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $nl2 = count($nomi);
    #aggiorno i nomi...
    for ($i = 0; $i < $nl2; $i++) {
        $sql = "INSERT INTO AUTORI (nome) VALUES ('" . $nomi[$i] . "') ON DUPLICATE KEY UPDATE nome = VALUES(nome)";
        $query = mysqli_query($db_connection, $sql) or die(mysql_error());
    }
    mysqli_close($db_connection);
}

#funzione inserimento nuovo utente

function aggiungiutente($nome, $a) {
    #leggo i nuovi nomi e li inserisco in array...
    $array = legginomi();
    while (strpos($nome, "  ") !== FALSE) {
        echo '<script type="text/javascript">alert("NAME NOT VALID! DETECTED CONSECUTIVE SPACE INSIDE FIELD NAME!");</script>';
        return;
    }
    $array2 = explode(",", $nome);
    $nl = count($array2);
    $l = count($array);
    for ($i = 0; $i < $nl; $i++) {
        $temp = $array2[$i];
        $temp = trim($temp);
        $temp = ucwords($temp);
        #verifico se il nome è già presente...
        $array[$l] = $temp;
        $l++;
        $ris = cercanome($temp);
        if ($ris == False) {
            if ($a == 1) {
                #aggiorno i nomi se ci sono nomi da aggiungere...
                scrivinomi($array);
                echo '<script type="text/javascript">alert("' . $temp . ' inserted!");</script>';
            } else {
                echo '<script type="text/javascript">alert("' . $temp . ' not found!");</script>';
            }
        } else {
            if ($a == 1) {
                echo '<script type="text/javascript">alert("' . $temp . ' exists!");</script>';
            } else {
                echo '<script type="text/javascript">alert("' . $temp . ' found!");</script>';
            }
        }
    }
}

#data ultima sessione

function datasessione() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $sql = "SELECT data FROM sessione_data";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $row = mysqli_fetch_array($result);
    $data = $row['data'];
    mysqli_close($db_connection);
    return $data;
}

#ritorno la data come intero

function dataprec() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $sql = "SELECT data FROM DATA_ULTIMO_LANCIO";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $row = mysqli_fetch_array($result);
    $data = $row['data'];
    mysqli_close($db_connection);
    $data = trim($data);
    $data = substr($data, 0, 10);
    $data = str_replace("-", "", $data);
    #conversione della stringa in intero
    $data = intval($data);
    return $data;
}

#ritorno la data come una stringa

function datastring() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $sql = "SELECT data FROM DATA_ULTIMO_LANCIO";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $row = mysqli_fetch_array($result);
    $data = $row['data'];
    mysqli_close($db_connection);
    return $data;
}

#aggiorno data_ultimo_lancio con la data di ultimo lancio

function aggiornadata() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $a = date("Y-m-d H:i", time());
    $sql = "SELECT data FROM DATA_ULTIMO_LANCIO";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $row = mysqli_fetch_array($result);
    $sql = "DELETE FROM DATA_ULTIMO_LANCIO WHERE data='" . $row['data'] . "'";
    $query = mysqli_query($db_connection, $sql) or die(mysql_error());
    #aggiorno la data...
    $sql = "INSERT INTO DATA_ULTIMO_LANCIO (data) VALUES ('" . $a . "') ON DUPLICATE KEY UPDATE data = VALUES(data)";
    $query = mysqli_query($db_connection, $sql) or die(mysql_error());
    mysqli_close($db_connection);
}

?>