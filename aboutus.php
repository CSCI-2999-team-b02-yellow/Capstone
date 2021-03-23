<?php
session_start();
?>

<!DOCTYPE html>

<html lang="en">

    <head>

        <!-- documentation at http://getbootstrap.com/docs/4.1/, alternative themes at https://bootswatch.com/ -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">

        
        <link href="css/indexstyle.css" rel="stylesheet">
        <title>FAQ</title>

    </head>

    <body>

    <div class="header">
        <div class="links">
            <a class="active" href="index.php">Home</a>
            <a href="products.php">Products</a>
            <?php if(isset($_SESSION["accesslevel"])) {
                if ($_SESSION["accesslevel"] > 1) {
                    echo '<a href="addinventory.php">Add Inventory</a>';
                }
            }?>
            <?php if(isset($_SESSION["accesslevel"])) {
                if ($_SESSION["accesslevel"] > 1) {
                    echo '<a href="updateinventory.php">Update Products</a>';
                }
            }?>
            <a href="contactus.php">Contact Us</a>
            <a href="aboutus.php">FAQ</a>
            <?php if(isset($_SESSION["accesslevel"])) {
                if ($_SESSION["accesslevel"] > 1) {
                    echo '<a href="employees.php">Employees</a>';
                }
            }?>
            <?php if(!isset($_SESSION["username"])) {
                echo '<a href="login.php">Login</a>';
            }?>
            <?php if(isset($_SESSION["username"])) {
                echo '<a href="logout.php">Logout</a>';
            }?>
        </div>
    </div>

	<br><br>


        <main class="container p-5">
				
			<div>
				<b>Frequently Asked Questions (FAQ)</b>
			</div>
			<br>
			
			<div style="visibility: hidden; position: absolute; width: 0px; height: 0px;">
			  <svg xmlns="http://www.w3.org/2000/svg">
				<symbol viewBox="0 0 24 24" id="expand-more">
				  <path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/><path d="M0 0h24v24H0z" fill="none"/>
				</symbol>
				<symbol viewBox="0 0 24 24" id="close">
				  <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/><path d="M0 0h24v24H0z" fill="none"/>
				</symbol>
			  </svg>
			</div>

			<details open>
			  <summary>
				When is this store open?
				<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
				<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
			  </summary>
				The store is open Sunday through Friday 9am-9pm
			</details>

			<details>

			  <summary>
				What is the stores return policy?
				<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
				<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
			  </summary>
			  Items can be returned in original condition with a valid reciept within 30 days of purchase. For items without a reciept or outside the 30 day window all returns will be up to manager discrestion. 
			</details>

			<details>
			  <summary>
				Our founders story...  
				<svg class="control-icon control-icon-expand" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#expand-more" /></svg>
				<svg class="control-icon control-icon-close" width="24" height="24" role="presentation"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#close" /></svg>
			  </summary>
				<p> The story of our company begins with our CEO and founder, Mr. N. Bolts. Mr Bolts, recently flush with cash from a lawsuit settlement from a large hardware store investe
				literally all of his money into Gamestop stock and Bitcoin, selling both at the absolute best times and making enough to finally follow his dreams!</p>
				</p>Mr. Bolts, determined to never fail the working class DIYer had a dream that of opening a hardware store that was never out of stock of nuts or bolts, unlike
				other larger stores. Investing his new found returns he made this store a reality and named it Nuts and Bolts, after himself.</p>
				<p>Today Nuts and Bolts is a thriving local hardware store that does its best to serve the local community and keeps nuts and bolts of all sizes in stock 94% of the time.
				</p>
			</details>
						
        </main>

        <footer>
		<div>
            Service provided by YellowTeam 2021
		</div>
        </footer>

    </body>

</html>
