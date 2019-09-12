=== Identityplus ===
Contributors: shfarr
Tags: authentication, security, 2factor, comments, spam, VPN, tls authentication, SSL client certificate, device identity, identity in the browser, two factor, login, two step authentication, password, admin, mobile, multi-factor, android, iphone, sso, strong two-step verification
Requires at least: 3.9
Tested up to: 4.9.8
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Identityplus is a novel security solution based on PKI (Public Key Infrastructure) called a network of trust. It features an all-in-one 2 (ocasionally 3) factor authentication and TLS level authentication making your site more secure than ever. Additionally it enables site owners to collaborate in defending against criminality by allowing them to send feedback on certificates and their oweners. With Identityplus, when a spam is reported, we are not only preventing the same spam being posted anywhere else, we are effectively preventing the spammer sending any other kind of spam, anywhere else. Keep on reading for a brief intro into this powerful technology.
	
= Log In, Before A Login Page =
Why Identityplus Is Better Than Any 2 Factor Authentication ...

Whenever you deal with application level login, whether it's one factor, two factor or any factor for that matter, you need a login page. This page must load before it gets the chance to see who is visiting, which is why Worpress has a protection against repeated login attempts. This can stop bots, to a certain degree, but if you happen to have an application vulnerability that can be used by a hacker to bypass login, whether you forgot to updated your Wordpress or something totally out of your control like zero day vulnerability in PHP, your blog is toast, regardless of how many factors of authentications you have.
Identityplus uses TLS level authentication, which means the visiting device is authenticated before the login page loads. If the proper PKI credentials are not presented by the device, the page will never, ever load. The visitor is simply directed away from the sensitive page and hence is unable to perform any kind of attack, be that brute force, credential theft or zero day for that matter. No login page, no problem ...

= A VPN Into Your Admin Panel =
Make Your Admin Panel Accessible Only From Your Computers ...

Having a PKI indenity in your browser is a powreful thing. Because the server expects that identity to be there, it does not only limit access by the user, it also limits access based on computer. As such, your admin panel becomes literally inaccessible from any other computer in the world. To access your admin panel, a hacker must steal your computer and access it from there.

= SSO Like Never Before =
Simpler, Faster, More Secure. Sign In Without Having To Do Anyting ...

Once you start using Identityplus, you will see you are hardly asked to do anything, you'll just notice you are logged in. Don't get scared, you are logged in because your computer is certified and it's being identified before you would have the chance to do anything. But since you also logged in with your password or your fingerprint into the device you are using (laptop / mobile phone), you are actually performing 2 factor authentication without even noticing it. You will occasionally notice however, as your certificate becomes idle, that you are being asked for your Identityplus PIN. That's actually the third factor authentication, all in one solution

= A Network Of Trust =
Reward Good Deeds And Block The Spammer, Not The Only Spam ...

When devices wear an impossible to forge identity, something amazing happens: if you restrict access to your comment section to devices with Identityplus certificates, whever you approve a comment, you are sending tokens of trust to the owner of that certificate telling Identityplus that you trust the owner. Now other blogs can trust him too, and he is steadily building a profile that defferentiates him from any malicius bot. Conversely, when you mark a comment as spam, you'll be telling Identityplus that this is a malicious entity, and we block the certificate making sure the device can't be used to post spam again. Now we are no longer only stopping spam, we are collectively working on stopping the spammer.

= Enjoy 10 Connected Users For Free =
Free Certificates, Free API Up To 10 Connected Users, Unlimited Validations For Free ...

A connected user is a user that can be signed in automatically via Identityplus into a service using Identityplus. If that service is your personal blog, you probably don't have more than 10 users who regularly sign into the administrative section of your Wordpress installation. If that's the case, you will never have to pay for Identityplus. Visitors that comment with Identityplus accounts that are not connected to local accounts do not count. For this reason the plugin will only connect administrator accouns by default. If you need log more than 10 users into your back-end, you'll need a business account, the cost of which scales with the number of your active users. Check our the pricing section for details.

== Installation ==
A step by step installation instruction of the Identityplus WordPress plugin ...

Hopefully you will not encounter difficulties during the installation process but if you, feel free to send us a support request and we'll help clarify things. That said, the installation does not require you to have any special knowledge, just follow the steps and enjoy the end result:

= DOWLOAD & ACTIVATE THE PLUGIN =
1. You will need access to your Worpress installation files, and we recommend that you have the latest Worpress although we've tested the plugin back to Wordpress 3.9.
2. We recommend you start by downloading the Identityplus Worpress Plugin.
3. Upload it into the /wp-content/plugins directory of your Wordpress, alongside your other plugins, using your favorite method (ftp, sftp, scp, etc...)
4. Activate the plugin and go to the Settings/Identityplus section. You will see an error that the certificate is missing but that is normal at this stage.

= SIGN UP FOR Identityplus AND AUTHENTICATE YOUR BROWSER =
1. Sign up for an Identityplus account, if you haven't already.
2. Install a certificate on your browser to access all the sections of your Identityplus account.
3. We recommend you certify your other devices at this stage (mobile, tabled, whatever you have).
4. Please don't forget to set up a PIN, you will have to use it occasionally if your certificate becomes idle.

= ISSUE AN API CERTIFICATE FOR YOUR WEB SITE =
1. In your Identityplus dashboard, hit "Advanced" and select "API Domains"
2. Add your blog's domain. For example if your blog can be found at http://www.myblog.me, then the domain you register should be "www.myblog.me".
3. After adding it you need to verify your ownership of the domain, by downloading a file from Identityplus, uploading it into the root of your website and than click verify. Sorry, but this is an essential security step, both for you to make sure you specified the domain correctly but also to prevent others from impersonating your site.
4. Now you can go to the "API Certificates" section, click "Add Web Site". Follow the steps to issue the certificate: select the domain, select the type of certificate and hit next.
5. At this stage you will have access to the password the certificate will be encrypted with. Copy it into the clipboard and paste it into the designated space in the Identityplus configuration in your Wordpress.
6. Download the certificate from your Identityplus Dashboard and upload it into the Identityplus settings of your Worpress instance. (hit save settings)

= VERIFY =
1. If everything went well so far, your local wordpress admin user is already bound to your Identityplus account and you are almost done.
2. You can see this in the "Behavior" section. Make sure your user is bound before you continue to prevent locking yourself out of your Wordpress.
3. Best way to test this, is by taking your other device that is connected with Identityplus, the one you don't regularly use to visit your /wp-admin section, and go to your bolgs /wp-admin section. If you are logged in automatically, your are all set.
4. Alternatuvely, you can selectively delete all the cookies that were set by your blog to invalidate your session and log in.
5. You can also test it by trying to log out of Wordpress (this will delete your authentication cookies). If you are logged back in immediately Identityplus is working.

= CONFIGURE = 
1. By checking "Enforce Identityplus Device Certificate" you make sure access to your filtered pages can only be done with valid Identityplus certificates.
2. If you do not want users to register with your Wordpress and you know only you are accessing the admin section you can also tick "Lock Down". This means that even if the user is comming with a valid Identityplus certificate, but that certificate is not any one that is already connected, access will be denied.
3. That is all, no more bots on your login page. You can also enforce the use of Identityplus certificates for commenting, this will give you the power to block the spammer whenever you mark a comment as spam and be an active participant in the Network of Trust.
4. You can try accessing your wp-admin section from a different computer, see what happens and enjoy piece of mind.

= EXTREME MEASURES =
1. If the certificate in your browser expires, or you manually revoke it you will not be able to access your blog. This conflict needs to be resolved on Identityplus. Simply issue a new certificate for your browser, install it and all will be back to normal.
2. You lose your device and it's connected to your Identityplus. Take your other device, go to Identityplus and revoke the certificate of your lost device. This will revoke access to any Identityplus bound account, so you are safe.
3. You locked your self out of your Wordpress. No problem. You need to go to your Worpress back-end, (access the files). In your wp-content/uploads/.../, you will find the certificate file you uploaded (a *.p12 keystore file). Delete the file. This will disable the plugin, and you can use your regular wordpress login to access your back-end.

== Changelog == 

== 1.6.4 ==
Minor bug fix

== 1.6.3 ==
Moved the legacy certificate validation endpoint from https://get.identity.plus to https://signon.identity.plus. The get endpoint will now exclussively handle the certificate issuing and installation process.

If you encounter problems while using legacy redirect and you land on get. subdomain, simply click the "back to single sign on" link to return to original flow. Please update your plugin to avoid this behavior. Sorry for the inconvenience. 

== 1.6.2 ==
Minor bug fix

== 1.6.1 ==
Minor bug fix

== 1.6 ==
Migrated to v1.1 Identityplus API. Identityplus plugin now allows individual wordpress users to connect their accounts on-demand. This new version also lifted the 10 accounts limit for non-corporate certificates, meaning that not-for-profit sites (public benefit or personal sites that produce no revenue) can connect any number of accounts at no cost.

= 1.5 =
Verified compatibility with Wordpress 4.9.8.
Corrected minor bugs.  

= 1.4 beta =
Verified compatibility with Wordpress 4.9.1.
Corrected minor bugs.  

= 1.2 beta =
Corrected WordPress coding practice issues and fixing  

= 1.1 beta =
We've restricted automatic login for pages that are filtered so that bots would not be bothered by the presence of the plugin. 

= 1.0 beta =
Version 1.0 beta is the first version of the Identityplus plugin, and it contains the minimum set of functionality and configuration options. Nevertheless, it will give your site an incredible security boost and at the same time it will improve user experience. Please take a moment to familiarize yourself with the core concepts so that you can take maximum advantage of this powerful security technology.

