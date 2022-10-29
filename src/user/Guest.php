<?php

namespace App\User;

class Guest
{
    public function __construct()
    {
    }
    /**
     * Generate the profile page for a guest user.
     * @param array $data The data to be used to generate the profile page.
     */
    public static function generate_profile($data)
    {
?>
        <div class="rela-block page">
            <div class="rela-block top-bar">
                <div class="caps name">
                    <div class="abs-center"><?= $data['fullName'] ?></div>
                </div>
            </div>
            <div class="side-bar">
                <div class="mugshot">
                    <div class="logo">
                        <svg viewbox="0 0 80 80" class="rela-block logo-svg">
                            <path d="M 10 10 L 52 10 L 72 30 L 72 70 L 30 70 L 10 50 Z" stroke-width="2.5" fill="none" />
                        </svg>
                        <p class="logo-text"><?= explode(" ", $data['fullName'])[0][0] ?> <?= explode(" ", $data['fullName'])[1][0] ?></p>
                    </div>
                </div>
                <p><?= $data['location'] ?></p>
                <p>Astoria, New York 11105</p>
                <p>1-800-CALLPLZ</p>
                <p><?= $data['email'] ?></p><br>
                <p class="rela-block social twitter"><?= $data['twitter'] ?></p>
                <p class="rela-block caps side-header">Expertise</p>
                <?php
                foreach ($data['skills'] as $expertise) {
                    echo "<p class='rela-block list-thing'>$expertise</p>";
                }
                ?>

                <p class="rela-block caps side-header">Education</p>

                <?php
                foreach ($data['education'] as $expertise) {
                    echo "<p class='rela-block list-thing'>$expertise</p>";
                }
                ?>
            </div>
            <div class="rela-block content-container">
                <h2 class="rela-block caps title"><?= $data['occupation'] ?></h2>
                <div class="rela-block separator"></div>
                <div class="rela-block caps greyed">Profile</div>
                <p class="long-margin"><?= $data['description'] ?> </p>
                <div class="rela-block caps greyed">Experience</div>

                <?php
                foreach ($data['jobs'] as $job) {
                ?>
                    <h3>Job #1</h3>
                    <p class="light">First job description</p>
                    <p class="justified">Plaid gentrify put a bird on it, pickled XOXO farm-to-table irony raw denim messenger bag leggings. Hoodie PBR&B photo booth, vegan chillwave meh paleo freegan ramps. Letterpress shabby chic fixie semiotics. Meditation sriracha banjo pour-over. Gochujang pickled hashtag mixtape cred chambray. Freegan microdosing VHS, 90's bicycle rights aesthetic hella PBR&B. </p>

                <?php
                }
                ?>

            </div>
        </div>
<?php
    }
}
?>
!Log19sin88cos