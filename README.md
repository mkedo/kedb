# Kedb

[![Build Status](https://travis-ci.org/mkedo/kedb.svg?branch=master)](https://travis-ci.org/mkedo/kedb)
[![Coverage Status](https://coveralls.io/repos/github/mkedo/kedb/badge.svg?branch=master)](https://coveralls.io/github/mkedo/kedb?branch=master)

Kedb is a database library that provides a data-access abstraction layer with consistent interface
and is focused on extensibility, testability and easier query decomposition.
It also has placeholders for passing data to queries.

## Example
Connection
```php
$dsn = "dbname=test port=5432 user=postgres password=''";
$db = new \Kedb\Spi\Postgresql\PgConnection($dsn);
$db->connect();
```

Simple query

```php
$query = new \Kedb\Query("SELECT ?:param p", ['param' => 'some string']);
$row = $query->exec($db)->assoc();
var_dump($row);
/*
 array(1) {
  [0] =>
  array(1) {
    'p' =>
    string(11) "some string"
  }
}
*/
```

Custom placeholder data type
```php
class WktGeom implements \Kedb\Formattable {
    private $wkt;
    private $epsg;
    /**
     * WktGeom constructor.
     * @param string $wkt
     * @param integer $epsg
     */
    public function __construct($wkt, $epsg)
    {
        $this->wkt = $wkt;
        $this->epsg = $epsg;
    }

    /**
     * @inheritdoc
     */
    public function format(\Kedb\SqlFormatter $formatter)
    {
        if (!empty($this->wkt)) {
            return (new \Kedb\Query("public.ST_SetSRID(public.ST_GeometryFromText(?), ?)", [$this->wkt, $this->epsg]))->format($formatter);
        }
        return $formatter->quoteLiteral(null);
    }
}

$query = new \Kedb\Query("INSERT INTO points (geom) VALUES (?:geom)", [
    'geom' => new WktGeom('POINT(1 1)', 3857)
]);
$query->exec($db);
// produces
// INSERT INTO points (geom) VALUES (public.ST_SetSRID(public.ST_GeometryFromText('POINT(1 1)'), 3857))
```
