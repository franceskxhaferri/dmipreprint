<?php
#funzione inserimento informazioni preprint

function insert_pubb($array, $uid) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
#adattamento stringhe pericolose per la query...
    $array[1] = addslashes($array[1]);
    $array[2] = addslashes($array[2]);
    $array[3] = addslashes($array[3]);
    $array[4] = addslashes($array[4]);
    $array[5] = addslashes($array[5]);
    $array[6] = addslashes($array[6]);
    $array[7] = addslashes($array[7]);
#generazione chiave
    $generato = rand();
    while (mysqli_num_rows(mysqli_query($db_connection, "SELECT * FROM PREPRINTS WHERE id_pubblicazione='" . $generato . "v1'") or die(mysql_error())) != 0) {
        $generato = rand();
    }
    $generato = $generato . "v1";
    $sql = "INSERT INTO PREPRINTS ( uid, id_pubblicazione, titolo, data_pubblicazione, autori, referenze, commenti, categoria, abstract) "
            . "VALUES ('" . $uid . "','" . $generato . "','" . $array[1] . "','" . date("c", time()) . "','" . $array[3] . "','" . $array[4] . "','" . $array[5] . "','" . $array[6] . "','" . $array[7] . "') ON DUPLICATE KEY UPDATE id_pubblicazione = VALUES(id_pubblicazione)";
    $query = mysqli_query($db_connection, $sql) or die(mysql_error());
#chiusura connessione al database
    mysqli_close($db_connection);
    return $generato;
}

#funzione inserimento informazioni preprint

function insert_p($array, $uid) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
#adattamento stringhe pericolose per la query...
    $array[1] = addslashes($array[1]);
    $array[2] = addslashes($array[2]);
    $array[3] = addslashes($array[3]);
    $array[4] = addslashes($array[4]);
    $array[5] = addslashes($array[5]);
    $array[6] = addslashes($array[6]);
    $array[7] = addslashes($array[7]);
    //query
    $sql = "INSERT INTO PREPRINTS ( uid, id_pubblicazione, titolo, data_pubblicazione, autori, referenze, commenti, categoria, abstract) "
            . "VALUES ('" . $uid . "','" . $array[0] . "','" . $array[1] . "','" . date("c", time()) . "','" . $array[3] . "','" . $array[4] . "','" . $array[5] . "','" . $array[6] . "','" . $array[7] . "') ON DUPLICATE KEY UPDATE id_pubblicazione = VALUES(id_pubblicazione)";
    $query = mysqli_query($db_connection, $sql) or die(mysql_error());
#chiusura connessione al database
    mysqli_close($db_connection);
}

#funzione che inserisce il pdf caricato all'interno dei database

function insertopdf($id) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $id = str_replace("-", "/", $id);
    //query
    $sql2 = "SELECT * FROM PREPRINTS WHERE id_pubblicazione='" . $id . "'";
    $query2 = mysqli_query($db_connection, $sql2) or die(mysql_error());
    $row = mysqli_fetch_array($query2);
    if ($handle = opendir($basedir)) {
        $i = 0;
        while ((false !== ($file = readdir($handle)))) {
            if ($file != '.' && $file != '..' && $file != 'index.php') {
                $idd = substr($file, 0, -4);
                if ($row['id_pubblicazione'] == $idd) {
                    $sql = "UPDATE PREPRINTS SET Filename='" . $file . "', checked='1' WHERE id_pubblicazione='" . $id . "'";
                    $query = mysqli_query($db_connection, $sql) or die(mysql_error());
                    $i++;
                    copy($basedir . $file, $copia . $file);
                    unlink($basedir . $file);
                }
            }
        }
#chiusura della directory...
        closedir($handle);
    }
#chiusura connessione al database
    mysqli_close($db_connection);
}

#funzione che inserisce un pdf all'interno dei database

function insertpdf($id, $type) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    //query
    $sql2 = "SELECT * FROM PREPRINTS WHERE id_pubblicazione='" . $id . "'";
    $query2 = mysqli_query($db_connection, $sql2) or die(mysql_error());
    $row = mysqli_fetch_array($query2);
    unlink($copia . $row['Filename']);
    if ($handle = opendir($basedir)) {
        $i = 0;
        while ((false !== ($file = readdir($handle)))) {
            if ($file != '.' && $file != '..' && $file != 'index.php') {
                $sql = "UPDATE PREPRINTS SET Filename= '" . $file . "', checked='1' WHERE id_pubblicazione='" . $id . "'";
                $query = mysqli_query($db_connection, $sql) or die(mysql_error());
                fclose($var);
                $i++;
                copy($basedir . $file, $copia . $file);
                unlink($basedir . $file);
            }
        }
#chiusura della directory...
        closedir($handle);
    }
#chiusura connessione al database
    mysqli_close($db_connection);
}

#funzione che visualizza lista upload

function leggiupload($uid) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    if (!isset($_GET['p'])) {
        $p = 1;
    } else {
        $p = $_GET['p'];
    }
    $risperpag = 5;
    $limit = $risperpag * $p - $risperpag;
    $sql = "SELECT * FROM PREPRINTS WHERE uid='" . $uid . "' AND checked='1'";
    $querytotale = mysqli_query($db_connection, $sql) or die(mysql_error());
    $ristot = mysqli_num_rows($querytotale);
    echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    echo "PAPERS UPLOADED: " . $ristot . "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    $npag = ceil($ristot / $risperpag);
#impostazione della navigazione per pagine
    if ($ristot != 0) {
        if ($p != 1) {
            $t1 = $p - 1;
            $t2 = $p - 2;
            $t3 = $p - 3;
            echo '<a style="color:#007897; text-decoration: none;" title="First page" href="uploaded.php?p=1&r=' . $uid . '"> &#8656 </a>';
            if ($p >= 3 && $t3 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p - 3) . '&r=' . $uid . '"> ' . " " . $t3 . " " . ' </a>';
            }
            if ($p >= 2 && $t2 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p - 2) . '&r=' . $uid . '"> ' . " " . $t2 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p - 1) . '&r=' . $uid . '"> ' . " " . $t1 . " " . ' </a>';
        }
        echo " " . $p . " ";
        if ($p != $npag) {
            $t4 = $p + 1;
            $t5 = $p + 2;
            $t6 = $p + 3;
            echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p + 1) . '&r=' . $uid . '"> ' . " " . $t4 . " " . ' </a>';
            if ($p < $npag && $t5 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p + 2) . '&r=' . $uid . '"> ' . " " . $t5 . " " . ' </a>';
            }
            if ($p < $npag && $t6 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p + 3) . '&r=' . $uid . '"> ' . " " . $t6 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="uploaded.php?p=' . $npag . '&r=' . $uid . '"> &#8658 </a>';
        }
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    $sql = "SELECT * FROM PREPRINTS WHERE uid='" . $uid . "' AND checked='1' ORDER BY data_pubblicazione DESC LIMIT " . $limit . "," . $risperpag . "";
    $result = mysqli_query($db_connection, $sql) or die(mysql_error());
    $i = $limit;
#recupero info e visualizzazione
    while ($row = mysqli_fetch_array($result)) {
        $i++;
        echo "<h1>" . $i . ".<br/></h1><div align='left' style='width:98%;'>";
        echo "<p><h1>Id of pubblication:</h1></p><div style='float:right;'><a style='color:#007897;' href=" . $copia . $row['Filename'] . " onclick='window.open(this.href);return false' title='" . $row['id_pubblicazione'] . "'>view</a>&nbsp&nbsp&nbsp<a title='Change this preprint' style='color:#007897;' href='./edit.php?id=" . $row['id_pubblicazione'] . "&r=" . $uid . "' onclick='window.open(this.href); return false'>edit</a></div><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
#echo "<p><h1>Id of pubblication:</h1></p><div style='margin-left:1%;'>" . $row['id_pubblicazione'] . "</div>";
        echo "<p><h1>Title:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . stripslashes($row['titolo']) . "</div>";
        echo "<p><h1>Date of pubblication:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . stripslashes($row['data_pubblicazione']) . "</div>";
        echo "<p><h1>Authors:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . stripslashes($row['autori']) . "</div>";
        echo "<p><h1>Journal reference:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . stripslashes($row['referenze']) . "</div>";
        echo "<p><h1>Comments:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . stripslashes($row['commenti']) . "</div>";
        echo "<p><h1>Category:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . stripslashes($row['categoria']) . "</div>";
        echo "<p><h1>Abstract:</h1></p><div style='margin-left:1%; margin-right:1%;'>" . stripslashes($row['abstract']) . "</div>";

        echo "</div><hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
#visualizzazione della navigazione per pagine
    if ($ristot != 0) {
        if ($p != 1) {
            echo '<a style="color:#007897; text-decoration: none;" title="First page" href="uploaded.php?p=1&r=' . $uid . '"> &#8656 </a>';
            if ($p >= 3 && $t3 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p - 3) . '&r=' . $uid . '"> ' . " " . $t3 . " " . ' </a>';
            }
            if ($p >= 2 && $t2 > 0) {
                echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p - 2) . '&r=' . $uid . '"> ' . " " . $t2 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p - 1) . '&r=' . $uid . '"> ' . " " . $t1 . " " . ' </a>';
        }
        echo " " . $p . " ";
        if ($p != $npag) {
            echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p + 1) . '&r=' . $uid . '"> ' . " " . $t4 . " " . ' </a>';
            if ($p < $npag && $t5 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p + 2) . '&r=' . $uid . '"> ' . " " . $t5 . " " . ' </a>';
            }
            if ($p < $npag && $t6 <= $npag) {
                echo '<a style="color:#007897; text-decoration: none;" href="uploaded.php?p=' . ($p + 3) . '&r=' . $uid . '"> ' . " " . $t6 . " " . ' </a>';
            }
            echo '<a style="color:#007897; text-decoration: none;" title="Last page" href="uploaded.php?p=' . $npag . '&r=' . $uid . '"> &#8658 </a>';
        }
        echo "<hr style='display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1em 0; padding: 0;'>";
    }
    $x = $limit + 1;
    echo "RESULTS FROM " . $x . " TO " . ($p * 5) . "<br/>";
    mysqli_close($db_connection);
}

#funzione che controlla la versione del preprint e lo archivia eventualmente

function version_preprintd($id1) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
#elaborazione dell'id...
    $lunghezza = strlen($id1);
    $id = substr($id1, 0, $lunghezza - 1);
#verifica se esistono preprints precedenti e li sposto...
    for ($i = 0; $i <= 20; $i++) {
        $sql = "SELECT * FROM PREPRINTS WHERE id_pubblicazione='" . $id . $i . "'";
        $query = mysqli_query($db_connection, $sql) or die(mysql_error());
        $row = mysqli_fetch_row($query);
        if (strcmp($id1, $row['1']) > 0) {
            #archiviazione preprints precedenti...
            $sql2 = "INSERT INTO PREPRINTS_ARCHIVIATI SELECT * FROM PREPRINTS WHERE id_pubblicazione='" . $id . $i . "' ON DUPLICATE KEY UPDATE id_pubblicazione = VALUES(id_pubblicazione)";
            $query2 = mysqli_query($db_connection, $sql2) or die(mysql_error());
            copy($copia . $row[9], $basedir4 . $row[9]);
            unlink($copia . $row[9]);
            #rimozione da preprints...
            $sql2 = "DELETE FROM PREPRINTS WHERE id_pubblicazione='" . $id . $i . "'";
            $query2 = mysqli_query($db_connection, $sql2) or die(mysql_error());
        }
    }
#chiusura connessione al database
    mysqli_close($db_connection);
}

#funzione controllo se ci sono preprint da approvare

function check_approve() {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    #verifica se esistono preprints precedenti e li sposto...
    $sql = "SELECT COUNT(*) AS TOTALFOUND FROM PREPRINTS WHERE checked='0'";
    $query = mysqli_query($db_connection, $sql) or die(mysql_error());
    $row = mysqli_fetch_array($query);
    #chiusura connessione al database
    mysqli_close($db_connection);
    if ($row['TOTALFOUND'] > 0) {
        return true;
    } else {
        return false;
    }
}

#funzione recupero informazioni degli account

function find_accounts($ord) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    #verifica se esistono preprints precedenti e li sposto...
    $sql = "SELECT * FROM ACCOUNTS ORDER BY " . $ord;
    $query = mysqli_query($db_connection, $sql) or die(mysql_error());
    #chiusura connessione al database
    mysqli_close($db_connection);
    return $query;
}

#funzione recupero informazioni degli account

function remove_accounts($accounts) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    $k = count($accounts);
    for ($i = 0; $i < $k; $i++) {
        $sql = "DELETE FROM ACCOUNTS WHERE email='" . $accounts[$i] . "'";
        $query = mysqli_query($db_connection, $sql) or die(mysql_error());
    }
    //chiusura connessione al database
    mysqli_close($db_connection);
}

#funzione recupero informazioni account

function find_account_info($email) {
    include './header.inc.php';
//import connessione database
    include './mysql/db_conn.php';
    #verifica se esistono preprints precedenti e li sposto...
    $sql = "SELECT * FROM ACCOUNTS WHERE email='" . $email . "'";
    $query = mysqli_query($db_connection, $sql) or die(mysql_error());
    $row = mysqli_fetch_array($query);
    #chiusura connessione al database
    mysqli_close($db_connection);
    return $row;
}

#invio mail agli admin quando avviene un nuovo submit(non terminata)

function sendmailadmin($uid, $idp) {
    mail("example@msn.com", "New preprint submitted by: " . $uid . " with id: " . $idp, "New preprint submitted by: " . $uid . " with id: " . $idp, "From: webmaster@{$_SERVER['SERVER_NAME']}\r\n" . "Reply-To: webmaster@{$_SERVER['SERVER_NAME']}\r\n" . "X-Mailer: PHP/" . phpversion());
}

?>