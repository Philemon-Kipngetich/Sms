<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Some College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include('header.php'); ?>
    <header class="hero bg-primary text-white text-center py-5">
        <div class="container">
            <h1>Welcome to Some College</h1>
            <p>Your path to a brighter future starts here.</p>
            <a href="application-form.php" class="btn btn-light btn-md bg-info">Apply Now</a>
        </div>
    </header>

    <!-- About Section -->
    <section class="about py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2>About Us</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deleniti totam aspernatur sed, blanditiis nisi, accusantium, omnis est at quidem dolores quae. Nisi vel illo velit impedit quae laudantium ratione architecto provident dolorem. Ducimus quod temporibus id nam, aliquam enim animi error ratione. Doloremque temporibus suscipit quod reprehenderit eum assumenda incidunt ex minus quae ullam, quasi consectetur, molestias cumque, et nesciunt corporis tempora animi quo veniam. Cum pariatur quibusdam illum placeat ducimus! Similique consequatur nulla numquam sunt expedita quos dolores mollitia cupiditate ex, ducimus at autem dolorum cum, facere itaque! Culpa accusantium unde hic eaque, neque facilis dolore necessitatibus ut ad.</p>
                </div>
                <div class="col-md-6">
                    <img src="images/some.jpg" alt="University Image" class="img-fluid">
                </div>
            </div>
        </div>
    </section>
    <?php
    include 'footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>