<?php

$cur_url = $_SERVER['REQUEST_URI'];
$tmpArray = explode('/', parse_url($cur_url, PHP_URL_PATH));
$gender_var = $tmpArray[2];

$conn = connect_db();

// Query categories
$sql = "SELECT category_id, category_name, description FROM category";
$categories = mysqli_query($conn, $sql);

// Query brands
$sql = "SELECT brand_id, brand_name, description FROM brand";
$brands = mysqli_query($conn, $sql);

$conn->close();

function get_brand_route($brand_id)
{
    global $cur_url, $gender, $gender_var;
    echo $cur_url . '/product?gender_id=' . $gender[$gender_var] . '&brand_id=' . $brand_id;
}

function get_category_route($category_id)
{
    global $cur_url, $gender, $gender_var;
    echo $cur_url . '/product?gender_id=' . $gender[$gender_var] . '&category_id=' . $category_id;
}

?>


<div class="home-container home-container-fluid">
    <div class="home-slide">
        <?php foreach ($brands as $category) : ?>
            <div class="item"
                 style="background-image: url('assets/images/<?php echo $gender_var ?>/brand/img<?php echo $category['brand_id'] ?>.jpg');">
                <div class="content">
                    <div class="name"><?php echo $category['brand_name'] ?></div>
                    <div class="description"><?php echo $category['description'] ?></div>
                    <button class="shop-btn"
                            onclick="location='<?php get_brand_route($category['brand_id']) ?>'">
                        See More
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="button">
        <button class="home-prev-btn"><i class="fa-solid fa-arrow-left"></i></button>
        <button class="home-next-btn"><i class="fa-solid fa-arrow-right"></i></button>
    </div>
    <div class="banner">
        <div class="banner-item">
            <img src="https://img.icons8.com/?size=50&id=11942&format=png&color=000000" alt="Free Shipping">
            <p>FREE SHIPPING<br><span>On Order Over $50</span></p>
        </div>
        <div class="banner-item">
            <img src="https://img.icons8.com/?size=50&id=104403&format=png&color=DC143CFF" alt="30-Days Returns">
            <p>7-DAYS RETURNS<br><span>Money-Back Guarantee</span></p>
        </div>
        <div class="banner-item">
            <img src="https://img.icons8.com/?size=50&id=11162&format=png&color=000000" alt="24/7 Support">
            <p>24/7 SUPPORT<br><span>Lifetime Support</span></p>
        </div>

    </div>
</div>
<section class="banner-section">
    <?php foreach ($categories as $category) : ?>
        <div class="banner-item1">
            <img alt="<?php echo $category['category_name'] ?>"
                 src="<?php echo 'assets/images/' . $gender_var . '/category/img' . $category['category_id'] . '.jpg' ?>">
            <div class="text-content">
                <div class="title"><?php echo $category['category_name'] ?></div>
                <div class="description"><?php echo $category['description'] ?></div>
                <button class="shop-btn"
                        onclick="location='<?php get_category_route($category['category_id']) ?>'">
                    See More
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<script src="assets/js/home.js"></script>
