## Download The Code

## Step 1 download the dependencies "composer install"

## Step 2 create database "emapta";

### run command "php artisan migrate" to run and create the tables

## table schema
```
mysql> desc events;
+---------------+---------------------+------+-----+---------+----------------+
| Field         | Type                | Null | Key | Default | Extra          |
+---------------+---------------------+------+-----+---------+----------------+
| id            | bigint(20) unsigned | NO   | PRI | NULL    | auto_increment |
| eventName     | varchar(255)        | NO   |     | NULL    |                |
| frequency     | varchar(100)        | NO   |     | NULL    |                |
| duration      | decimal(5,2)        | NO   |     | NULL    |                |
| startDateTime | datetime            | NO   |     | NULL    |                |
| endDateTime   | datetime            | YES  |     | NULL    |                |
| invitees      | text                | NO   |     | NULL    |                |
| created_at    | timestamp           | YES  |     | NULL    |                |
| updated_at    | timestamp           | YES  |     | NULL    |                |
+---------------+---------------------+------+-----+---------+----------------+
```

### routes
## run php artisan serve


## route create
http://localhost:8000/api/events

## parameters
eventName,frequency,duration,startDateTime,endDateTime,invitees

## sample response
```
{"status":"Success","message":"Record Created"}
```



## route get instance
http://localhost:8000/api/events/instance?from=2021-01-31&to=2021-03-31

## response

```
[
    {
        "id": 1,
        "eventName": "testEvent 3",
        "frequency": "Monthly",
        "duration": "10.00",
        "startDateTime": "2021-01-31 18:05:00",
        "endDateTime": "2021-01-31 18:15:00",
        "invitees": "[1,2,3]",
        "created_at": "2021-07-14T05:15:00.000000Z",
        "updated_at": null
    },
    {
        "id": 2,
        "eventName": "testEvent 3",
        "frequency": "Monthly",
        "duration": "10.00",
        "startDateTime": "2021-02-28 18:05:00",
        "endDateTime": "2021-02-28 18:15:00",
        "invitees": "[1,2,3]",
        "created_at": "2021-07-14T05:15:00.000000Z",
        "updated_at": null
    }
]
```

