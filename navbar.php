<?php
global $pro_dir, $gender, $category;

$cart_item_count = 0;
$promos = fetch_promos();

if (is_logged_in()) {
    $user_id = $_SESSION['user']['user_id'];
    $conn = connect_db();
    $select_rows = mysqli_query($conn, "SELECT cart_item_id FROM `cart_item` WHERE user_id = $user_id") or die('query failed');
    $cart_item_count = mysqli_num_rows($select_rows);
    $conn->close();
}

$search_text = isset($_GET['search_text']) ? $_GET['search_text'] : "";

$tmpArray = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$base_url = $tmpArray[2];

$base_route = $pro_dir . $base_url . '/product?gender_id=';
$show_secondary_navbar = in_array($base_url, ['men', 'women', 'kids']);

function get_secondary_navs($gender_var)
{
    global $base_route, $gender, $category;
    return [
        'new' => [
            'text' => 'New In',
            'id' => 'new',
            'href' => $base_route . $gender[$gender_var]
        ],
        'tops' => [
            'text' => 'Tops',
            'id' => $category['tops'],
            'href' => $base_route . $gender[$gender_var] . '&category_id=' . $category['tops']
        ],
        'pants' => [
            'text' => 'Pants',
            'id' => $category['pants'],
            'href' => $base_route . $gender[$gender_var] . '&category_id=' . $category['pants']
        ],
        'shoes' => [
            'text' => 'Shoes',
            'id' => $category['shoes'],
            'href' => $base_route . $gender[$gender_var] . '&category_id=' . $category['shoes']
        ],
        'dressing' => [
            'text' => 'Dressing',
            'id' => $category['dressing'],
            'href' => $base_route . $gender[$gender_var] . '&category_id=' . $category['dressing']
        ],
        'accessories' => [
            'text' => 'Accessories',
            'id' => $category['accessories'],
            'href' => $base_route . $gender[$gender_var] . '&category_id=' . $category['accessories']
        ],
        'bags' => [
            'text' => 'Bags',
            'id' => $category['bags'],
            'href' => $base_route . $gender[$gender_var] . '&category_id=' . $category['bags']
        ],
        'sportswear' => [
            'text' => 'Sportswear',
            'id' => $category['sportswear'],
            'href' => $base_route . $gender[$gender_var] . '&category_id=' . $category['sportswear']
        ],
        'outerwear' => [
            'text' => 'Outerwear',
            'id' => $category['outerwear'],
            'href' => $base_route . $gender[$gender_var] . '&category_id=' . $category['outerwear']
        ]
    ];
}

function fetch_promos()
{
    $conn = connect_db();
    $sql = "SELECT promotion_name, promo_code, type, `value`, min_subtotal FROM promotion 
           WHERE disabled = 0
           ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt->execute()) {
        $_SESSION['form_data'] = ['error' => 'Error while retrieving promotions: ' . $stmt->error];
        $stmt->close();
        $conn->close();
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit();
    }

    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
    return $result;
}

?>

<!-- Primary Navbar -->
<nav id="primary-navbar" class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <!-- Brand Logo -->
        <a class="navbar-brand" style="width: fit-content" href="<?php echo $pro_dir ?>home">
            <i class="fa fa-palette"></i> FASHION PALETTE
        </a>
        <!-- Toggle button for mobile view -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#primary-navbar-content"
                aria-controls="primaryNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Primary Navbar Content -->
        <div id="primary-navbar-content" class="collapse navbar-collapse">
            <ul class="navbar-nav" style="width: fit-content">
                <!-- Main Links -->
                <li class="category-icons nav-item text-center">
                    <a class="nav-link primary-nav-link"
                       href="<?php echo $pro_dir ?>women" data-category="women">WOMEN</a>
                </li>
                <li class="category-icons nav-item text-center">
                    <a class="nav-link primary-nav-link"
                       href="<?php echo $pro_dir ?>men" data-category="men">MEN</a>
                </li>
                <li class="category-icons nav-item text-center">
                    <a class="nav-link primary-nav-link"
                       href="<?php echo $pro_dir ?>kids" data-category="kids">KIDS</a>
                </li>
                <?php if (is_admin()) { ?>
                    <li class="category-icons nav-item text-center dropdown">
                        <a class="nav-link primary-nav-link" href="<?php echo $pro_dir ?>admin"
                           id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
                           aria-expanded="false">ADMIN</a>
                        <div id="admin-dropdown" class="dropdown-menu" aria-labelledby="adminDropdown">
                            <a class="dropdown-item" href="<?php echo $pro_dir ?>admin/user">
                                <i class="fa-solid fa-user pr-3"></i>Users</a>
                            <a class="dropdown-item" href="<?php echo $pro_dir ?>admin/product">
                                <i class="fas fa-box pr-3"></i>Products</a>
                            <a class="dropdown-item" href="<?php echo $pro_dir ?>admin/order">
                                <i class="fa-brands fa-shopify pr-3"></i>Orders</a>
                            <a class="dropdown-item" href="<?php echo $pro_dir ?>admin/promotion">
                                <i class="fa-solid fa-receipt pr-3"></i>Promotions</a>
                        </div>
                    </li>
                <?php } else if (is_supplier()) { ?>
                    <!-- Supplier  routes -->
                <?php } ?>
            </ul>
            <div style="width: 100%">
                <?php if ($show_secondary_navbar) : ?>
                    <input id="search-bar" class="search-bar form-control mr-2" type="search"
                           placeholder="Search" aria-label="Search" value="<?php echo $search_text ?>">
                <?php endif; ?>
            </div>
            <ul class="navbar-nav ml-auto flex-fill" style="width: fit-content">
                <?php if (is_logged_in()) { ?>
                    <!-- User profile and cart -->
                    <li class="nav-item ml-0 dropdown">
                        <a class="nav-link" href="<?php echo $pro_dir ?>my" id="userDropdown" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="nav-icon fas fa-user"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="<?php echo $pro_dir ?>my/profile">
                                <i class="fas fa-user-circle pr-2"></i> My Profile</a>
                            <a class="dropdown-item" href="<?php echo $pro_dir ?>my/orders">
                                <i class="fa-brands fa-shopify pr-2"></i> My Orders</a>
                            <form method="post" style="display: inline;">
                                <button class="dropdown-item" type="submit" name="logout">
                                    <i class="fas fa-sign-out-alt pr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </li>
                    <li class="nav-item ml-0">
                        <a class="nav-link" href="<?php echo $pro_dir ?>favourite">
                            <i class="nav-icon fas fa-heart"></i>
                        </a>
                    </li>
                    <li class="nav-item ml-0">
                        <a class="nav-link" href="<?php echo $pro_dir ?>cart">
                            <i class="nav-icon fa-solid fa-cart-shopping"></i>
                            <?php if ($cart_item_count > 0) : ?>
                                <span class="item-count badge badge-pill badge-light">
                                    <?php echo $cart_item_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php } else { ?>
                    <li class="category-icons nav-item" style="width: 90px">
                        <a class="nav-link" href="<?php echo $pro_dir ?>login">SIGN IN</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>


<!-- Secondary Navbar -->
<?php if ($show_secondary_navbar) : ?>
    <nav id="secondary-navbar" class="navbar navbar-expand-lg px-0">
        <div class="d-flex flex-column w-100">
            <?php if (isset($promos) && $promos->num_rows > 0) : $promo_count = $promos->num_rows ?>
                <div id="promos" class="carousel slide" data-ride="carousel">
                    <?php
                    $promos = $promos->fetch_all(MYSQLI_ASSOC);
                    $index = 0;
                    foreach ($promos as $promo): ?>
                        <div class="carousel-item text-center <?php if ($index === 0) {
                            echo 'active';
                        } ?>">
                            <div class="promo-info">
                                <p class="promo-text"><?php echo $promo['promotion_name'] ?></p>
                                <?php if (isset($promo['min_subtotal'])): ?>
                                    <p class="promo-text">
                                        SPEND OVER $<?php echo $promo['min_subtotal'] ?></p>
                                <?php endif; ?>
                                <?php if ($promo['type'] === 'Amount'): ?>
                                    <p class="promo-text">$<?php echo $promo['value'] ?> OFF</p>
                                <?php endif; ?>
                                <?php if ($promo['type'] !== 'Amount'): ?>
                                    <p class="promo-text"><?php echo $promo['value'] ?>% OFF</p>
                                <?php endif; ?>
                                <p class="promo-text">
                                    APPLY PROMO CODE: <b><?php echo $promo['promo_code'] ?></b></p>
                            </div>
                        </div>
                        <?php
                        $index++;
                    endforeach; ?>

                    <?php if ($promo_count > 1): ?>
                        <a class="carousel-control-prev" href="#promos" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#promos" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="container">
                <!-- Toggle button for mobile view -->
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#secondary-navbar-content"
                        aria-controls="secondaryNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Secondary Navbar Content -->
                <div id="secondary-navbar-content" class="collapse navbar-collapse">
                    <ul class="navbar-nav mx-auto">
                        <!-- Dynamically Rendered Links -->
                        <?php foreach (get_secondary_navs($base_url) as $route): ?>
                            <li class="nav-item">
                                <a class="nav-link text-center" id="<?php echo $route['id'] ?>"
                                   href="<?php echo $route['href'] ?>">
                                    <?php echo $route['text'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
<?php endif; ?>

<!-- JavaScript to handle dynamic rendering -->
<script>
    let lastScroll = window.scrollY;
    window.addEventListener("scroll", function () {
        const currentScroll = window.scrollY;
        if (Math.abs(currentScroll - lastScroll) < 100) {
            return;
        }
        if (currentScroll < lastScroll) {
            if (document.getElementById("primary-navbar")) {
                document.getElementById("primary-navbar").style.top = '0';
            }
            if (document.getElementById("secondary-navbar")) {
                document.getElementById("secondary-navbar").style.top = '60px';
            }
        } else {
            if (document.getElementById("primary-navbar")) {
                document.getElementById("primary-navbar").style.top = '-100px';
            }
            if (document.getElementById("secondary-navbar")) {
                document.getElementById("secondary-navbar").style.top = '-100px';
            }
        }
        lastScroll = currentScroll;
    });

    document.addEventListener('search', function () {
        const search_text = document.getElementById('search-bar').value;
        const searchParams = new URLSearchParams(window.location.search);
        search_text ? searchParams.set('search_text', search_text) : searchParams.delete('search_text');

        if (window.location.href.endsWith('men') || window.location.href.endsWith('/kids')) {
            const tmpArray = window.location.href.split('/');
            console.log(tmpArray)
            switch (tmpArray[4]) {
                case "men":
                    searchParams.set('gender_id', '1');
                    break;
                case "women":
                    searchParams.set('gender_id', '2');
                    break;
                default:
                    searchParams.set('gender_id', '3');
                    break;
            }
            window.location.href = window.location.href + '/product?' + searchParams.toString();
        } else {
            window.location.search = searchParams.toString()
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (document.querySelectorAll("a.nav-link.active")) {
            document.querySelectorAll("a.nav-link.active")
                .forEach(li => {
                    li.classList.remove("active-primary");
                    li.classList.remove("active-secondary");
                    li.classList.remove("active-primary-icon");
                    li.attributes.removeNamedItem("aria-current");
                });
        }

        // find the link to the current page and make it active

        const href = location.pathname.includes('admin') ||
        location.pathname.includes('my') ||
        location.pathname.includes('kids') ||
        location.pathname.includes('men') ? location.pathname.split('/').slice(0, 3).join('/') : location.pathname;

        let element = document.getElementById('primary-navbar-content');
        element.querySelectorAll(`a[href="${href}"].nav-link`)
            .forEach(a => {
                if (location.pathname.includes('admin') ||
                    location.pathname.includes('kids') ||
                    location.pathname.includes('men')) {
                    a.classList.add("active-primary");
                } else {
                    console.log("called")
                    a.classList.add("active-primary-icon");
                }
                a.setAttribute("aria-current", "page");
            });

        element = document.getElementById('secondary-navbar-content');
        if (element) {
            const searchParams = new URLSearchParams(window.location.search);
            const id = searchParams.get('category_id') ? searchParams.get('category_id') : 'new';
            element.querySelectorAll(`a[id="${id}"].nav-link`)
                .forEach(a => {
                    a.classList.add("active-secondary");
                    a.setAttribute("aria-current", "page");
                });
        }
    });
</script>
