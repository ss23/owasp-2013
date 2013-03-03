# A1-Injection

> Injection flaws, such as SQL, OS, and LDAP injection occur when untrusted data is sent to an interpreter as part of a command or query. The attacker’s hostile data can trick the interpreter into executing unintended commands or accessing unauthorized data.

> <cite>https://www.owasp.org/index.php/Top_10_2013-Risks</cite>
<!-- TODO: Make that cite actually cite -->

## Introduction to Injection

While injection can cover a large (really, *large*) range of exploits, the most common one is probably the SQL injection. For those of you familiar with SQL, this should be painfully obvious. Yet, people keep getting it wrong.
Hopefully this should help get you up to speed on what SQL Injection is, how to prevent it, and the different ways to leverage it in an attack.

### SQL Primer

If you're not familiar with SQL, chances are you can find a nicer primer out there than this, but hopefully this will get you up to speed with the basics of what you need to know.
SQL is a language to access relationship databases. Imagine some table, and each of the tables have relationships to each other. So, you have a Product, which might have a Seller (that is, a company that sells this product). The tables for that might involve the product having an ID, a Title, and a Description. The Seller might involve an ID, Name, and Address. Then, a field for linking them might be Product having a Seller_id. If you're familiar with OOP, you might be able to draw parallels from there.

So, lets say we want to create these tables:
```sql
CREATE TABLE products (id INT, title TEXT, description TEXT, seller_id INT);
CREATE TABLE seller   (id INT, name TEXT, address TEXT);
```
Next, you'll want to insert data into these tables.
```sql
INSERT INTO seller   (id, name, address) VALUES (1, "Sella", "123 Awesome Road");
INSERT INTO products (id, title, description, seller_id) VALUES (1, "Auction Site", "Get you own instant Auction site! (May contain SQLi)", 1);
```

And at this point, you might percieve a problem. What if you want to be able to insert products based on what a user puts in on a form? Lets say we had users, and they put in their username, our SQL would now contain some of their input.

### The Injection

Funny story, my username for this site is actually `ss23"); DROP TABLE users;`. As you might imagine, the intention here is to delete the entire Users table, including all of it's data. The SQL query might look like this:
```sql
INSERT INTO users (id, username) VALUES (1, "ss23"); DROP TABLE users;
```

In certian situations, this would actually delete all the data in that table (so don't try it on live sites unless you like prison).

## Our Application

Before, you look at the code, lets see if we can use this chance to see whether it is capable of SQL injection.

### Heuristic detection

After learning a little about SQL, you might have an idea of the kinds of characters that could cause an error. Imagine inserting a ".
```sql
SELECT FROM users WHERE username = "ss23"";
```
The unmatched quote is going to cause some errors. It's from this kind of thing we can coax out some diagnostics about whether we can inject a page or not.

Look what happens when we simply put in a single quote:
> Fatal error: Uncaught exception 'PDOException' with message 'SQLSTATE[HY000]: General error: 1 unrecognized token: "'"'

An error like that is a fairly sure sign that there's some injection to be found. So, lets carry on and see if we can figure out what the query might look like. From there, we can working on coaxing out the information we want.

### The Query

Since it's a search query, we can be fairly sure there's a select, and given it's only one field, we can get a pretty good idea of what it might look like. In these situations, there are two kinds of queries that are likely, and they're very similar.
```sql
SELECT FROM products WHERE description LIKE '%our input%';
SELECT FROM products WHERE description = 'our input';
```
In SQL, % is a wildcard. Since this is a search, it seems likely the first kind of query is most likely, however, we can construct a query that will work in either case.

### Getting our data

The data you might want to get depends on the site, but a common target would be the users. Now as for getting it, even if we know the query, it might be a hard step. A common way of getting data out though, is UNION. Basically, it says "Append the result of this query with the result of the last one". So, we can easily select the results of the two tables.
```sql
SELECT * FROM products WHERE description LIKE '%s' UNION SELECT * FROM users
```

A query like that should show us some juicy information. So, to generate that query, we just set our input to `s' UNION SELECT * FROM users`. But wait! That is still giving us an error, about a rogue %? You might already realise, but when we looked at the possible queries, our input was wedged between two %'s. Luckily, even SQL has the idea of comments. You can use a space, followed by two dashes, followed by another space, to represent the same as would be `//` in C (that is, a comment spanning the rest of the line).
So, lets change our input to `s' UNION SELECT * FROM users -- `. Great! Now it works!
We now have a list of users and their passwords.
Feel free to play around with various different strings. See if you can get some of the products displayed with it, or see if you can restrict it to only display one user.

## Code Review

Now we know the person who wrote this code is incompetent, but where do we go from here?

### Getting the code

Lets look at the current code and see if we can tell why it's so wrong (apart from being written in PHP).
Take a look at (insert URL here), and look at the index.php.
```php
$query = $db->query("select * from products where description LIKE '%{$s}%'");
```
That's the relevant line. It's just putting our user input into that query. Generally, anything like this is going to be horrible. While it's another category according to OWASP, you can think of XSS as another kind of this. You take user input and don't escape it for the medium. Escaping just means "turning the special characters into something not special". Keep in mind, this changes depending on what we're using it for, which is another big mistake people make. Removing characters like < and > is essential for XSS prevention, but not for SQL injection prevention.
So, how do we fix this?

### Bound parameters

If your language supports it, then you should use bound parameters where possible. Keep in mind, this exists for more than just SQL, so whenever you have a injection vector, you can likely use something like this.
Bound parameters, or prepared statements, are statements which you construct with the specific intention of having place holder for user input. Then, when you put the user input in, your language can treat the input specially to avoid it having special characters.
Lets look at an example in PHP.
```php
$query = $db->prepare('select * from users where username = :user');
$query->bindValue(':user', $_POST['user']);
$query->execute();
```
As for what's going on here, we're just telling PHP that the content of :search is user input, so to treat it special and 'fix' its special characters. For SQL, this is mainly to do with the quote characters.

### Escaping manually

There is an alternative to bound parameters, and that's escaping the strings yourself. Take for example shell injection. That's the same kind of thing, but instead of injecting code into a SQL server, we're injecting code that goes to a shell prompt (imagine a hacker being able to type whatever commands they want into your system!).
```php
pass_thru('ls ' . $_GET['directory']); // Allow a user to view the contents of a directory
```
Normally, this should just display the contents of a directory (which is somewhat of an issue already, do you want people looking at your /home or C:/Users/ directory?). However, enter Eve. The directory she wants to look at is `f; rm -rf /`. You might be able to tell what happens next -- the system has all its files deleted.
To prevent this, we can escape the user input. In PHP, it looks like this
```php
pass_thru('ls ' . escapeshellarg($_GET['directory']); // A more secure way to view the contents of a directory
```
So there you have it, we can manually take the user input and turn all the special characters to not special ones.

### Fix it yourself!

Now with the knowledge of prepared statements and escaping, try fixing the index.php. I suggest prepared statements. Remember to use the PHP manual if you get stuck. Once you're done, compare with the answer in the /fixed directory and see how close you were. Even better, try running the script yourself and see if you can still inject it!

## Going further

### Automation

There are already a lot of tools to do what we did manually, and they'll do it a lot faster, too. While they may not catch every possible vector to inject, they would save a lot of time. Check out [SQLMap](http://sqlmap.org) for example. Try running it against our test code and see whether it can get a list of the users for you!

### SQL specific injection

First of all, if you want to know more about SQL injection, you're probably going to have to learn one of the vendor specific dialects. The syntax we used for this example should work for most vendors implmentations, but you would be surprised how different SQLite is to MySQL, to MSSQL.
If you do need specific vendor info in a hurry, try looking for a SQL injection cheat sheet like http://pentestmonkey.net/category/cheat-sheet/sql-injection -- they'll likely have most of the information need in a pinch.

### Code execution from SQL Injection

More information relevant to SQL injection specifically is that you can leverage this kind of exploit to get full remote code execution. Lets start with an easy case, Microsoft SQL Server on Windows XP:
```
EXEC xp_cmdshell 'net user';
```
You can execute commands as easily as that!

If we want to hit more targets, it's going to get a bit harder.
A useful technique for getting some code execution is to get the SQL server to write out a file with some code to a place in the web root. For example, in our application, if it was MySQL instead of SQLite, we might be able to do something like this:
```sql
SELECT * FROM products WHERE description LIKE '%d' UNION SELECT '<?php exec($_GET["c"]); ?>' INTO OUTFILE '/var/www/shell.php' -- %'
```
Now you simple browse to `shell.php?c=ls -lah`, and you have a shell!

### Authentication Bypass

So along with the other kinds of things we've seen you can do, you can also log in as any (or an admin) user a lot of the time. Consider the following example:
```
SELECT * FROM `users` WHERE `username` LIKE 'foo' AND `password` LIKE 'bar'
```
There are a lot of things we could do with something as simple as this. Consider the wildcard % in SQL. What if we put our username as % and our password as `password`. As you can probably tell, we'll be logged in as one of the users who happens to have password as their password (which is far more likely than you might first think!).
What if you wanted to make sure you authenticate as the administration account? Well, you can probably assume that either the administration account is called "admin", or perhaps that the ID of the administration account is 1, so what about a query that looks like:
```
SELECT * FROM `users` WHERE `username` LIKE 'foo' AND `password` LIKE '' OR id = 1 -- '
# Or...
SELECT * FROM `users` WHERE `username` LIKE 'admin' AND `password` LIKE '' OR 1 = 1 -- '
```

Really, all you need to do is be creative. SQL Injection opens up a world of different things you can do to the host. There are hundreds of ways to leverage a simple injection exploit, and we can't cover them all here.
So, never have them, because as you can see, it could lead to a complete compromise before you know it.
