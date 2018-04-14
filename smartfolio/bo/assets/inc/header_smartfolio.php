<header id="header">
    <nav>
        <ul>
            <li>
                <a href="../">
                    <i class="fas fa-arrow-circle-left"></i>
                    Retour
                </a>
            </li>
            <li>
                <a <?php App::SetLink('index.php'); ?>>
                    <i class="far fa-list-alt"></i>
                    Portefeuilles
                </a>
            </li>
            <?php
            if (User::AmIManager()) {
                ?>
                <li>
                    <a href="#">
                        <i class="fas fa-users"></i>
                        Utilisateurs
                    </a>
                    <ul class="submenu">
                        <li>
                            <a <?php App::SetLink('agents.php'); ?>>
                                <i class="fas fa-users"></i>
                                Agents
                            </a>
                        </li>
                        <li>
                            <a <?php App::SetLink('clients.php'); ?>>
                                <i class="far fa-address-book"></i>
                                Clients
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a <?php App::SetLink('nv_port.php'); ?>>
                        <i class="fas fa-plus-circle"></i>
                        Nouveau portefeuille
                    </a>
                </li>
                <?php
            }
            ?>
            <li>
                <a href="#">
                    <i class="far fa-user-circle"></i>
                    <?php echo $_SESSION['user']['name']; ?>
                </a>
                <ul class="submenu">
                    <li>
                        <a <?php App::SetLink('profil.php'); ?>>
                            <i class="far fa-user"></i>
                            Profil
                        </a>
                    </li>
                    <?php
                    if (User::AmIManager()) {
                        ?>
                        <li>
                            <a <?php App::SetLink('setup.php?pair'); ?>>
                                <i class="fas fa-cog"></i>
                                Paramètres
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <li>
                        <a href="../?logout">
                            <i class="fas fa-sign-out-alt"></i>
                            Déconnexion
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</header>