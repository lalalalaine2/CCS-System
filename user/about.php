<?php
require_once "../includes/authentication.php";
check_user_login();

$page_title = "About Us";
include_once "../includes/_head.php";
?>

<link rel="stylesheet" href="../css/navbar.css">
<link rel="stylesheet" href="../css/about.css">

<body>
    <?php include_once "includes/navbar.php"; ?>

    <div class="about-container">
        <div class="about-content">
            <h1>COLLEGE OF COMPUTING STUDIES</h1>
            <div class="description">
                <p>The College of Computing Studies at Western Mindanao State University is dedicated to excellence in computing education. We prepare students for successful careers in the rapidly evolving field of technology through comprehensive programs in computer science, information technology, and related disciplines.</p>
                
                <p>Our mission is to:</p>
                <ul>
                    <li>Provide high-quality education in computing and information technology</li>
                    <li>Foster innovation and research in computing sciences</li>
                    <li>Develop skilled professionals ready for the demands of the digital age</li>
                    <li>Promote ethical practices in technology</li>
                </ul>

                <p>With state-of-the-art facilities and experienced faculty members, we ensure our students receive both theoretical knowledge and practical skills necessary for success in the technology industry.</p>
            </div>
        </div>
    </div>

    <?php include_once "../includes/_footer.php"; ?>
</body>
</html>
