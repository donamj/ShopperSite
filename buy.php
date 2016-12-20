<?php
session_start();
?>
<html>
<head>
	<title>Buy Products</title>
</head>
<body>
	<?php
			error_reporting(E_ALL);
			ini_set('display_errors','On');
			$user_agent = urlencode($_SERVER['HTTP_USER_AGENT']);
			$ip_address = urlencode($_SERVER['REMOTE_ADDR']);
	
			$cat = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent=$user_agent&visitorIPAddress=$ip_address&trackingId=7000610&categoryId=72&showAllDescendants=true');
					
			
		?>
	<center>
	<h2>Shopping Cart:</h2>
	<?php
		if(isset($_GET['clear']))
		{
			unset($_SESSION['cart']);
		}
		if(!isset($_SESSION['cart']))
		{
			$_SESSION['cart'] = array();
		}
	
		function getProductFromCart($prod_id)
		{
			$count = 0;
			foreach($_SESSION['cart'] as $item)
			{
				if($prod_id == $item['id'])
				{
					return $count;
				}
				$count++;
			}
			return -1;
		}

		if(isset($_GET['delete']))
		{
			$prod_index = getProductFromCart($_GET['delete']);
			if($prod_index != -1)
			{
				unset($_SESSION['cart'][$prod_index]);
				$_SESSION['cart'] = array_values($_SESSION['cart']);
			}
		}

		function ifProductExists($prod_id)
		{
			foreach($_SESSION['cart'] as $item)
			{
				if($prod_id == $item['id'])
				{
					return true;
				}
			}
		return false;	
		}

		function addToCart($product_bought)
		{
			$list = new SimpleXMLElement($_SESSION['search_result']);
			foreach($list->categories->category->items->product as $x)
			{
				if ($product_bought == $x['id']) 
				{
					$y = array("id"=>(int)$x['id'],
						"image"=>(string)$x->images->image[0]->sourceURL,
						"shoppingURL"=>(string)$x->productOffersURL,
						"name"=>(string)$x->name,
						"price"=>(int)$x->minPrice);
					$_SESSION['cart'][] = $y;
					break;
				}
			}
		}

		if (isset($_GET['buy'])) 
		{
			$item_id = (int)$_GET['buy'];
			if (!ifProductExists($item_id) && isset($_SESSION['search_result'])) 
			{
				
				addToCart($item_id);
			}
		}

		$total_cart_value = 0;
		if(sizeof($_SESSION["cart"]) > 0)
		{

	?>
	<table border="1">
		<tr>
		<th>Image</th>
		<th>Name</th>
		<th>Price</th>
		<th></th>
		</tr>
	<?php
		foreach ($_SESSION['cart'] as $item) 
		{
			$total_cart_value = $total_cart_value +$item['price'];
	?>
		<tr>
		<td><a href="<?=$item['shoppingURL']?>" >
		<img src="<?=$item['image']?>"/></a></td>
		<td><?=$item['name']?></td>
		<td>$<?=$item['price']?></td>
		<td><a href="buy.php?delete=<?=$item['id']?>">Remove</a></td>
		</tr>
	<?php
		}
	?>
	</table>
	<?php
		}
	?>
	<h3>Total Cart Value: $<?= $total_cart_value ?></h3>
	
	<form action="buy.php" method="GET">
		<input type="hidden" name="clear" value="1"/>
		<input type="submit" value="Empty Cart"/>
	</form>

	<form action="buy.php" method="GET">
		<hr>
		<h2>Choose your product </h2>
		<label>Category </label>
		<select name="category">
			<option>Computers</option>
			<?php
					$xml = new SimpleXMLElement($cat);
					foreach ($xml->category->categories->category as $x) 
					{
			   			$m = $x->name[0];
			   			print "<optgroup label=$m>";
				   		if (empty($x->categories) == false) 
				   		{
				   			foreach ($x->categories->category as $y ) 
				   			{
				   				$n = $y->name[0];
				   				$val = $y["id"];
				   				print "<option value='$val'>$n</option>";		
				   			}
				   		}
			   			print("</optgroup>");
					}
			?>
		</select>
		<label>Keyword </label>
		<input type="text" name="search"></input>
		<input type="submit" value="Search"></input>

	</form>

	<?php
		if (isset($_GET['search']))
		{
			$cat_item = $_GET['category'];
			$search_item = $_GET['search'];
			
			$url = 'http://sandbox.api.shopping.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId='.$cat_item.'&keyword='.$search_item.'&numItems=20';
			$res = file_get_contents($url);
			$xml_res = new SimpleXMLElement($res);
			$_SESSION['search_result'] = $res;
			if(empty($xml_res->categories->category->items->product)==false)
			{
	?>
	<table border=1>
		<tr><th>Image</th><th>Item</th><th>Price</th><th>Description</th></tr>

		<?php
			foreach ($xml_res->categories->category->items->product as $a) 
			{
		?>
		<tr><td><a href="buy.php?buy=<?=$a['id']?>">
			<img src="<?=$a->images->image->sourceURL?>"/></a></td>
			<td><?=$a->name?></td>
			<td><?=$a->minPrice?></td>
			<td><?=$a->fullDescription?></td>
		</tr>
	<?php
	}
	}
	?>
	</table>
	<?php
		}
	?>
	</center>
</body>
</html>
