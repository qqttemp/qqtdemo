<div class="row">
<div class="block-title text-center margin-bottom-50">
  <h3 class="text-uppercase"><?php echo $heading_title; echo ' Items' ?></h3>
  <span class="text-uppercase">Our Bestseller Products</span>
</div>
<div class="module-slider bestsellers owl-carousel">
  <?php foreach ($products as $product) { ?>
  <div class="product-layout">
    <div class="product-thumb transition clearfix">
      <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>" class="img-responsive" /></a></div>
      <div class="caption">
         <div class="min-height-caption"><h4><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></h4>
        <?php $str = $product['description']; ?>
        <?php if (strlen($str) > 65) {
        $str = substr($str, 0, 62) . '...'; ?>
        <p class="para"> <?php echo $str; ?> </p>
      <?php } ?></div>
      <hr>

        <?php if ($product['rating']) { ?>
        <div class="rating">
          <?php for ($i = 1; $i <= 5; $i++) { ?>
          <?php if ($product['rating'] < $i) { ?>
          <span class="fa fa-stack"><i class="fa fa-star-o fa-stack-2x"></i></span>
          <?php } else { ?>
          <span class="fa fa-stack"><i class="fa fa-star fa-stack-2x"></i><i class="fa fa-star-o fa-stack-2x"></i></span>
          <?php } ?>
          <?php } ?>
        </div>
        <?php } ?>
        <?php if ($product['price']) { ?>
        <p class="price pull-left">
          <?php if (!$product['special']) { ?>
          <?php echo $product['price']; ?>
          <?php } else { ?>
          <span class="price-new"><?php echo $product['special']; ?></span> <span class="price-old"><?php echo $product['price']; ?></span>
          <?php } ?>
          <?php /* if ($product['tax']) { ?>
          <span class="price-tax"><?php echo $text_tax; ?> <?php echo $product['tax']; ?></span>
          <?php } */ ?>
        </p>
        <?php } ?>

        <div class="hidden-btn-blk">
          <button type="button" data-toggle="tooltip" title="<?php echo $button_compare; ?>" onclick="compare.add('<?php echo $product['product_id']; ?>');"><i class="fa fa-exchange"></i></button>
        </div>
        <div class="pull-right block-btn-blk">
        <button type="button" onclick="cart.add('<?php echo $product['product_id']; ?>');"><i class="fa fa-shopping-cart"></i><?php /* <span class="hidden-xs hidden-sm hidden-md"><?php echo $button_cart; ?></span> */ ?></button>
        <button class="wishlist-top" type="button" data-toggle="tooltip" title="<?php echo $button_wishlist; ?>" onclick="wishlist.add('<?php echo $product['product_id']; ?>');"><i class="fa fa-heart"></i></button>
        
      </div>

      </div>
      
    </div>
  </div>
  <?php } ?>
  </div>
</div>
<script type="text/javascript"><!--
$('.bestsellers').owlCarousel({
  items: 4,
  itemsDesktop : [1199,4],
  itemsDesktopSmall : [980,2],
  itemsTablet: [768,1],
  itemsTabletSmall: false,
  itemsMobile : [479,1],
  autoPlay: false,
  navigation: true,
  navigationText: ['<i class="fa fa-chevron-left fa-5x"></i>', '<i class="fa fa-chevron-right fa-5x"></i>'],
  pagination: false
});
--></script>