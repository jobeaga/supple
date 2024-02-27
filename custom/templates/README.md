# Custom templates

These HTML templates define the full website in Supple-template format. Examples on `templates\main\*`

## Some Supple-template sintax:

Access tables and data: suppose you have the table `person` with at least three columns: `active`, `name` and `birthdate`. If you define a custom bean for this table, and a function called `age()` you can use all of that in this way, generating the following HTML:  
```
<h1>List of people</h1>
<ul>
    $person(active==1){
        <li>$name (age: $person.age())</li>
    }(name)
</ul>
```
Ths list is ordered by name, and filtered by active.

You can also write plain conditions, with no table:
```
<div> $($amount > 0)[You have $amount in your account][Your account is empty] </div>
```

Another way of iterating, is defining a number, and going through it:
```
$[15](=count)

$count{ Repeat $number ! <br> }
```
