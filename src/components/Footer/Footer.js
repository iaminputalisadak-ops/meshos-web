import React from 'react';
import { Link } from 'react-router-dom';
import './Footer.css';

const Footer = () => {
  return (
    <footer className="footer">
      <div className="container">
        <div className="footer-content">
          {/* Column 1: Shop Non-Stop */}
          <div className="footer-column">
            <h3 className="footer-heading">Shop Non-Stop on Meesho</h3>
            <p className="footer-subtext">
              Trusted by crores of Indians
            </p>
            <p className="footer-subtext">
              Cash on Delivery | Free Delivery
            </p>
            <div className="app-download-buttons">
              <a 
                href="https://play.google.com/store" 
                target="_blank" 
                rel="noopener noreferrer"
                className="app-button google-play"
              >
                <i className="fab fa-google-play"></i>
                <div>
                  <span className="app-button-small">GET IT ON</span>
                  <span className="app-button-large">Google Play</span>
                </div>
              </a>
              <a 
                href="https://apps.apple.com" 
                target="_blank" 
                rel="noopener noreferrer"
                className="app-button app-store"
              >
                <i className="fab fa-apple"></i>
                <div>
                  <span className="app-button-small">Available on the</span>
                  <span className="app-button-large">App Store</span>
                </div>
              </a>
            </div>
          </div>

          {/* Column 2: Careers */}
          <div className="footer-column">
            <h3 className="footer-heading">Careers</h3>
            <ul className="footer-links">
              <li>
                <Link to="/supplier">Become a supplier</Link>
              </li>
              <li>
                <Link to="/hall-of-fame">Hall of Fame</Link>
              </li>
              <li>
                <Link to="/sitemap">Sitemap</Link>
              </li>
            </ul>
          </div>

          {/* Column 3: Legal and Policies */}
          <div className="footer-column">
            <h3 className="footer-heading">Legal and Policies</h3>
            <ul className="footer-links">
              <li>
                <a href="https://blog.meesho.com" target="_blank" rel="noopener noreferrer">
                  Meesho Tech Blog
                </a>
              </li>
              <li>
                <Link to="/notices">Notices and Returns</Link>
              </li>
            </ul>
          </div>

          {/* Column 4: Reach out to us */}
          <div className="footer-column">
            <h3 className="footer-heading">Reach out to us</h3>
            <div className="social-media-icons">
              <a 
                href="https://facebook.com" 
                target="_blank" 
                rel="noopener noreferrer"
                className="social-icon facebook"
                aria-label="Facebook"
              >
                <i className="fab fa-facebook-f"></i>
              </a>
              <a 
                href="https://instagram.com" 
                target="_blank" 
                rel="noopener noreferrer"
                className="social-icon instagram"
                aria-label="Instagram"
              >
                <i className="fab fa-instagram"></i>
              </a>
              <a 
                href="https://youtube.com" 
                target="_blank" 
                rel="noopener noreferrer"
                className="social-icon youtube"
                aria-label="YouTube"
              >
                <i className="fab fa-youtube"></i>
              </a>
              <a 
                href="https://linkedin.com" 
                target="_blank" 
                rel="noopener noreferrer"
                className="social-icon linkedin"
                aria-label="LinkedIn"
              >
                <i className="fab fa-linkedin-in"></i>
              </a>
              <a 
                href="https://twitter.com" 
                target="_blank" 
                rel="noopener noreferrer"
                className="social-icon twitter"
                aria-label="Twitter"
              >
                <i className="fab fa-twitter"></i>
              </a>
            </div>
          </div>

          {/* Column 5: Contact Us */}
          <div className="footer-column">
            <h3 className="footer-heading">Contact Us</h3>
            <div className="contact-info">
              <p>Meesho Technologies Private Limited</p>
              <p>CIN: U62099KA2024PTC186568</p>
              <p>
                3rd Floor, Wing-E, Helios Business Park,<br />
                Kadubeesanahalli Village, Varthur Hobli,<br />
                Outer Ring Road Bellandur, Bangalore,<br />
                Bangalore South, Karnataka, India, 560103
              </p>
              <p>
                <a href="mailto:query@meesho.com">E-mail address: query@meesho.com</a>
              </p>
              <p className="copyright">© 2015-2026 Meesho.com</p>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;

