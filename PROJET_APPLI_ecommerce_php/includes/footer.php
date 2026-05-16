    </main>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>golabelesandale</h3>
                    <p>Votre destination pour des produits de qualité exceptionnelle.</p>
                    <div class="social-links social-icons">
                   <a href="#"><i class="bi bi-facebook"></i></a>
                   <a href="#"><i class="bi bi-instagram"></i></a>
                   <a href="#"><i class="bi bi-twitter-x"></i></a>
                   </div>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="/ecommerce-php/index.php">Accueil</a></li>
                        <li><a href="/ecommerce-php/products.php">Produits</a></li>
                        <li><a href="/ecommerce-php/about.php">À propos</a></li>
                        <li><a href="/ecommerce-php/contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Informations</h4>
                    <ul>
                        <li><a href="#">Conditions générales</a></li>
                        <li><a href="#">Politique de confidentialité</a></li>
                        <li><a href="#">Livraison</a></li>
                        <li><a href="#">Retours</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Newsletter</h4>
                    <p>Inscrivez-vous pour recevoir nos offres exclusives</p>
                    <form class="newsletter-form" method="POST" action="/ecommerce-php/newsletter.php">
                        <input type="email" name="email" placeholder="Votre email" required>
                        <button type="submit">S'inscrire</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> golabelesandale. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
    
    <script src="/ecommerce-php/js/main.js"></script>
    

</body>
</html>
