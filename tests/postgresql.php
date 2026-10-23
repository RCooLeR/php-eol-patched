<?php
/* The runner supplies disposable credentials over stdin or a FastCGI request body. */
$config = PHP_SAPI === 'cli'
    ? json_decode(file_get_contents('php://stdin'), true) : $_POST;
$results = array('sapi' => PHP_SAPI);
$certificate = tempnam('/tmp', 'postgresql-ca-');
file_put_contents($certificate, $config['certificate']);
$connection = null;

function postgresqlCheck($name, $valid) {
    global $results;
    $results['check:' . $name] = $valid ? 'ok' : 'failed';
}

try {
    $connectionString = 'host=postgresql port=5432 dbname=' . $config['database']
        . ' user=' . $config['username'] . ' password=' . $config['password']
        . ' sslmode=verify-full sslrootcert=' . $certificate . ' connect_timeout=5';
    $connection = pg_connect($connectionString, PGSQL_CONNECT_FORCE_NEW);
    postgresqlCheck('pgsql-connect', $connection !== false);
    if (!$connection) { throw new RuntimeException('PostgreSQL connection failed'); }
    $version = pg_version($connection);
    $results['libpq_version'] = $version['client'];
    $results['server_version'] = $version['server'];
    $tls = pg_query($connection, 'SELECT ssl FROM pg_stat_ssl WHERE pid = pg_backend_pid()');
    postgresqlCheck('pgsql-tls', pg_fetch_result($tls, 0, 0) === 't');
    $value = "ordinary 'quoted' \\ value \xc3\xa9";
    $query = pg_query_params($connection,
        'SELECT $1::text AS value, $2::integer AS number, $3::text AS missing',
        array($value, 42, null));
    $row = pg_fetch_assoc($query);
    postgresqlCheck('pgsql-query-params', $row['value'] === $value
        && (int) $row['number'] === 42 && $row['missing'] === null);
    pg_prepare($connection, 'ordinary_prepared', 'SELECT $1::text');
    $query = pg_execute($connection, 'ordinary_prepared', array($value));
    postgresqlCheck('pgsql-prepare', pg_fetch_result($query, 0, 0) === $value);
    $query = pg_query($connection, 'SELECT ' . pg_escape_literal($connection, $value));
    postgresqlCheck('pgsql-escape-literal', pg_fetch_result($query, 0, 0) === $value);
    $table = 'postgresql_control_' . str_replace('.', '', uniqid('', true));
    pg_query($connection, 'BEGIN');
    pg_query($connection, 'CREATE TABLE ' . $table . ' (id integer PRIMARY KEY, value text)');
    $inserted = pg_insert($connection, $table, array('id' => 1, 'value' => $value));
    $selected = pg_select($connection, $table, array('id' => 1));
    $results['pgsql_insert_value'] = isset($selected[0]['value']) ? $selected[0]['value'] : 'missing';
    postgresqlCheck('pgsql-insert-select', $inserted !== false && count($selected) === 1
        && $selected[0]['value'] === $value);
    $updated = pg_update($connection, $table, array('value' => 'updated'), array('id' => 1));
    $selected = pg_select($connection, $table, array('id' => 1));
    postgresqlCheck('pgsql-update', $updated !== false && $selected[0]['value'] === 'updated');
    pg_query($connection, 'SAVEPOINT ordinary');
    pg_query_params($connection, 'UPDATE ' . $table . ' SET value = $1', array('rolled back'));
    pg_query($connection, 'ROLLBACK TO SAVEPOINT ordinary');
    $selected = pg_select($connection, $table, array('id' => 1));
    postgresqlCheck('pgsql-transaction', $selected[0]['value'] === 'updated');
    postgresqlCheck('pgsql-delete', pg_delete($connection, $table, array('id' => 1)) !== false);
    pg_query($connection, 'ROLLBACK');
    pg_close($connection);
    $connection = null;

    $dsn = 'pgsql:host=postgresql;port=5432;dbname=' . $config['database']
        . ';sslmode=verify-full;sslrootcert=' . $certificate;
    foreach (array(false, true) as $emulated) {
        $name = $emulated ? 'pdo-emulated' : 'pdo-native';
        $pdo = new PDO($dsn, $config['username'], $config['password'],
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                  PDO::ATTR_EMULATE_PREPARES => $emulated));
        $results['pdo_client_version'] = (string) $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
        postgresqlCheck($name . '-tls', (int) $pdo->query(
            'SELECT ssl::integer FROM pg_stat_ssl WHERE pid = pg_backend_pid()')->fetchColumn() === 1);
        $pdo->exec('CREATE TEMP TABLE ordinary_pdo (id integer PRIMARY KEY, value text, optional text)');
        $pdo->beginTransaction();
        $statement = $pdo->prepare('INSERT INTO ordinary_pdo VALUES (:id, :value, :optional)');
        $statement->bindValue(':id', 1, PDO::PARAM_INT);
        $statement->bindValue(':value', $value, PDO::PARAM_STR);
        $statement->bindValue(':optional', null, PDO::PARAM_NULL);
        $statement->execute();
        $pdo->commit();
        $statement = $pdo->prepare('SELECT value, optional FROM ordinary_pdo WHERE id = ?');
        $statement->execute(array(1));
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        postgresqlCheck($name . '-prepare', $row['value'] === $value && $row['optional'] === null);
        postgresqlCheck($name . '-quote', $pdo->query('SELECT ' . $pdo->quote($value))->fetchColumn() === $value);
        $pdo->beginTransaction();
        $pdo->exec("UPDATE ordinary_pdo SET value = 'rolled back'");
        $pdo->rollBack();
        postgresqlCheck($name . '-transaction',
            $pdo->query('SELECT value FROM ordinary_pdo')->fetchColumn() === $value);
        $pdo = null;
    }
    try {
        new PDO($dsn, $config['username'], $config['password'] . '-invalid');
        postgresqlCheck('password-required', false);
    } catch (PDOException $error) {
        postgresqlCheck('password-required', strpos($error->getMessage(), 'password authentication failed') !== false);
    }
} catch (Exception $error) {
    $results['error'] = str_replace($config['password'], '[redacted]', $error->getMessage());
} catch (Throwable $error) {
    $results['error'] = str_replace($config['password'], '[redacted]', $error->getMessage());
}
if ($connection) { pg_close($connection); }
unlink($certificate);
$results['pass'] = isset($results['error']) ? 'no' : 'yes';
foreach ($results as $name => $value) {
    if (strpos($name, 'check:') === 0 && $value !== 'ok') { $results['pass'] = 'no'; }
}
header('Content-Type: application/x-www-form-urlencoded');
echo http_build_query($results, '', '&');
if (PHP_SAPI === 'cli' && $results['pass'] !== 'yes') { exit(1); }
