<?php
declare(strict_types=1);

/**
 * Interface texts, English. Exact key parity with fr.php (French is the reference language).
 * Same parameters as the French file: :date, :page, :pages, :year, :privacy_url, :count, :host.
 */
return [
    'meta' => [
        'site_name' => 'Aéroboat France',
        'tagline' => 'Ground-effect aircraft models, designed by students',
        'default_description' => 'Aéroboat France, a student association based in Toulouse: design, construction and testing of ground-effect aircraft models. Discover the V6 project.',
        'test_banner' => 'Test version of the site: it is not indexed by search engines and its content may change.',
        'title_suffix' => ' | Aéroboat France',
    ],

    'nav' => [
        'home' => 'Home',
        'project' => 'The project',
        'team' => 'The team',
        'partners' => 'Partners',
        'news' => 'News',
        'join' => 'Join us',
        'contact' => 'Contact',
        'skip_to_content' => 'Skip to main content',
        'menu_open' => 'Open menu',
        'menu_close' => 'Close menu',
        'language_label' => 'Language',
        'main_label' => 'Main navigation',
        'lang_fr' => 'Français',
        'lang_en' => 'English',
    ],

    'common' => [
        'read_more' => 'Read more',
        'back' => 'Back',
        'send' => 'Send',
        'loading' => 'Loading…',
        'all' => 'All',
        'published_on' => 'Published on :date',
        'status' => [
            'done' => 'Completed',
            'current' => 'In progress',
            'planned' => 'Upcoming',
        ],
        'close' => 'Close',
        'prev' => 'Previous',
        'next' => 'Next',
        'zoom' => 'Enlarge image',
    ],

    'home' => [
        'hero' => [
            'title' => 'Aéroboat France',
            'lead' => 'We design and build ground-effect scale models, and we keep looking for ways to improve their aerodynamics, structures and instrumentation.',
            'cta_primary' => 'Join the team',
            'cta_secondary' => 'Contact us',
        ],
        'about' => [
            'kicker' => 'Chasing ground effect',
            'title' => 'About us',
            'text' => 'It all started as a two-student project at the end of high school, inspired by Soviet ground-effect aircraft, the ekranoplans. Since then we have built several scale models to find out which shapes make the best use of ground effect. The association was founded in the summer of 2025 by three students. We design, test and measure, with two goals: make this physics accessible and fun, and see how far it can be put to practical use. If you love aeronautics or model-making, you are welcome.',
        ],
        'founders' => [
            'title' => 'Founding members',
            'text' => 'Three friends with the same passion for aeromodelling. We joined forces in April 2025 to create a structure dedicated to ground-effect aircraft, and we made our first public appearance at the 2025 Paris Air Show (Salon du Bourget).',
        ],
        'contact' => [
            'title' => 'Contact us',
            'text' => 'Want to join us, become a partner, or simply talk about ground effect? Write to us anytime.',
            'button' => 'Write to the team',
        ],
        'cta' => [
            'title' => 'Want to join the adventure?',
            'text' => 'Into aeronautics, model-making or ground effect? Tell us who you are.',
            'button' => 'Join us',
        ],
    ],

    'project' => [
        'hero' => [
            'kicker' => 'V6 project',
            'title' => 'Toward a new generation of ground-effect craft',
            'lead' => 'For six years, Aéroboat France has been developing ground-effect aircraft prototypes. After the successful flight tests of the HV3 and HV4 models, we are launching the V6, an important step in our journey.',
        ],
        'why' => [
            'title' => 'Why a V6?',
            'text' => 'Every prototype has taught us something. The goal of the V6 is easy to state and harder to achieve: build a reliable, high-performance and modular ground-effect craft that pushes the current limits of the technology.',
            'v3' => 'The HV3 taught us that we need a rigid structure and properly sized floats.',
            'v4' => 'The HV4 validated several solutions (tail design, buoyancy, ease of assembly), but it also showed its limits, mainly on waterproofing, wing rigidity and payload.',
            'v6' => 'The V6 is therefore a synthesis. It keeps what worked, fixes what caused problems and lets us try new ideas.',
        ],
        'objectives' => [
            'title' => 'Our objectives',
            'items' => [
                ['title' => 'Aerodynamic performance', 'text' => 'Optimise the wing geometry (aspect ratio, dihedral, sweep) and test new configurations, such as downward winglets or integrated floats.'],
                ['title' => 'Versatility', 'text' => 'Design a model that can fly both in and out of ground effect, to validate our calculations and widen its uses.'],
                ['title' => 'Reliability and simplicity', 'text' => 'Quick assembly in under 30 minutes, robust connections and a stiffer structure.'],
                ['title' => 'Optimised buoyancy', 'text' => 'Keep an overall density lower than water thanks to watertight foams, with a low waterline even in waves.'],
                ['title' => 'Increased payload', 'text' => 'Carry more payload while keeping the empty weight under 2 kg. A real lightweight-design challenge.'],
            ],
        ],
        'v6' => [
            'title' => 'Innovations of the V6',
            'lead' => 'The main design choices made at this stage of the project.',
            'items' => [
                ['title' => 'Low wings', 'text' => 'Wings of 100 cm on each side with a 50 cm chord, to get the most out of ground effect while staying stable.'],
                ['title' => 'Carbon-fibre T-tail', 'text' => 'It has to stay rigid without suffering from the disturbed air behind the wing.'],
                ['title' => 'Watertight foams', 'text' => 'An overall density lower than water, so the craft sits low even in waves.'],
                ['title' => 'Removable landing gear', 'text' => 'Designed to protect watertightness.'],
                ['title' => 'Enhanced watertightness', 'text' => 'The wing joints and the electronics cockpit are protected with solutions inspired by boat building.'],
                ['title' => 'Streamlined floats', 'text' => 'To glide better and limit spray, with internal cavities that prevent any risk of sinking.'],
                ['title' => 'In-flight adjustable angle of incidence', 'text' => 'A path we are exploring: adjusting lift to the phase of flight.'],
            ],
            'note_html' => 'The V6 is <strong>still being designed</strong>: these choices may still change.',
        ],
        'electronics' => [
            'title' => 'Advanced electronics',
            'text' => 'The V6 relies on a dual-computer architecture, with automatic recording to an SD card. We are also looking at motors to manoeuvre when stationary.',
        ],
        'timeline' => [
            'title' => 'Project milestones',
            'items' => [
                ['date' => 'Step 1', 'title' => 'HV3', 'text' => 'First experimental prototype, tested in flight. It showed us how important a rigid structure and well-sized floats are.', 'status' => 'done'],
                ['date' => 'Step 2', 'title' => 'HV4', 'text' => 'Second experimental prototype, tested in flight. It validated several solutions and revealed limits to fix.', 'status' => 'done'],
                ['date' => 'Step 3', 'title' => 'V6: design', 'text' => 'We are defining the new generation of models, building on what the earlier prototypes taught us.', 'status' => 'current'],
                ['date' => 'Step 4', 'title' => 'V6: construction and testing', 'text' => 'Construction, then validation of our calculations to widen the model’s uses.', 'status' => 'planned'],
            ],
        ],
        'gallery' => [
            'title' => 'Gallery',
            'lead' => 'Water tests, workshop, simulations and 3D renders: the project in pictures.',
        ],
    ],

    'team' => [
        'hero' => [
            'title' => 'The team',
            'lead' => 'The people who design, build and test Aéroboat France’s models.',
        ],
        'who' => [
            'title' => 'Who are we?',
            'text' => 'We are a team made up mostly of students, passionate about aeronautics, model-making and the science of ground effect. We design, build and test large-span models, because it is by getting our hands dirty that we really understand.',
        ],
        'role_default' => 'Member',
        'photo_note' => 'Every photo is published with the person’s consent. To change or remove it, write to us or use the “Exercise my rights” page.',
    ],

    'partners' => [
        'hero' => [
            'title' => 'Partners',
            'lead' => 'They support the Aéroboat France project.',
        ],
        'categories' => [
            'school' => 'School',
            'technology' => 'Technology',
            'support' => 'Support',
        ],
        'ionis' => [
            'title' => 'IONIS Education Group',
            'text' => 'IONIS Scholarship: support from IONIS Education Group for the project',
            'badge' => 'School partner',
        ],
        'dx' => [
            'title' => '3DEXPERIENCE',
            'text' => 'Thank you to 3DEXPERIENCE, which has supported us for 4 years. Every year we receive technical guidance and, in November, licenses for all interested members of the association (20 licenses available). With these professional-grade tools, we carry out our projects seriously.',
            'licenses_label' => '20 licenses for members',
            'years_label' => '4 years of support',
            'others_label' => '3DEXPERIENCE also supports other associations.',
        ],
        'become' => [
            'title' => 'Become a partner',
            'text' => 'Want to support a student aeronautics project? Write to us, we would be glad to talk about it.',
            'button' => 'Contact us',
        ],
        'visit' => 'Visit website',
    ],

    'news' => [
        'hero' => [
            'title' => 'News',
            'lead' => 'News from the project and the team.',
        ],
        'empty' => 'No article has been published yet.',
        'by' => 'By',
        'back' => 'Back to news',
        'page_of' => 'Page :page of :pages',
    ],

    'join' => [
        'hero' => [
            'title' => 'Join us',
            'lead' => 'Are you a student, or passionate about aeronautics, model making or ground effect? Tell us who you are and what you would like to bring to the project.',
        ],
        'fields' => [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email address',
            'school' => 'School or institution',
            'level' => 'Level of study',
            'skills' => 'Skills',
            'motivation' => 'Motivation',
            'availability' => 'Availability',
            'cv' => 'CV (PDF, optional)',
        ],
        'hints' => [
            'cv' => 'PDF files only, 5 MB maximum. It is stored in a folder that cannot be accessed from the internet.',
            'motivation' => 'A few lines are enough: what attracts you to the project and what you would like to bring to it.',
        ],
        'consent_html' => 'I agree that Aéroboat France may use this information to review my application and reply to me. Learn more in the <a href=":privacy_url">privacy policy</a>.',
        'consent_short' => 'Consent',
        'submit' => 'Send my application',
        'sent' => [
            'title' => 'Application sent',
            'text' => 'Thank you! We have received your application and will get back to you by email.',
        ],
        'steps' => [
            ['title' => 'Fill in the form', 'text' => 'Introduce yourself and tell us what motivates you. The CV is optional.'],
            ['title' => 'We review your application', 'text' => 'The team reads every application carefully.'],
            ['title' => 'We reply to you', 'text' => 'We get back to you by email at the address you provided.'],
        ],
    ],

    'contact' => [
        'hero' => [
            'title' => 'Contact',
            'lead' => 'A question, a partnership proposal or a press enquiry? Write to us.',
        ],
        'fields' => [
            'name' => 'Name',
            'email' => 'Email address',
            'subject' => 'Subject',
            'body' => 'Message',
        ],
        'consent_html' => 'I agree that Aéroboat France may use this information to reply to my message. Learn more in the <a href=":privacy_url">privacy policy</a>.',
        'consent_short' => 'Consent',
        'submit' => 'Send message',
        'sent' => [
            'title' => 'Message sent',
            'text' => 'Thank you! We have received your message and will reply as soon as possible.',
        ],
        'info' => [
            'title' => 'Contact details',
            'email_label' => 'Email',
            'phone_label' => 'Phone',
            'city_label' => 'City',
        ],
    ],

    'rights' => [
        'hero' => [
            'title' => 'Exercise my rights',
            'lead' => 'You can ask for access to your data, have it corrected or erased, restrict its use, object to its processing or request its portability. This form is for that.',
        ],
        'fields' => [
            'name' => 'Name',
            'email' => 'Email address',
            'request_type' => 'Type of request',
            'body' => 'Your request',
        ],
        'types' => [
            'access' => 'Access my data',
            'rectify' => 'Correct my data',
            'erase' => 'Erase my data',
            'restrict' => 'Restrict the processing of my data',
            'object' => 'Object to a processing',
            'portability' => 'Receive my data (portability)',
        ],
        'notice_html' => 'The information you enter is used only to handle your request to exercise your rights, in compliance with a legal obligation. It can be viewed only by authorised members and is kept for 12 months. Learn more in the <a href=":privacy_url">privacy policy</a>.',
        'submit' => 'Send my request',
        'sent' => [
            'title' => 'Request sent',
            'text' => 'Thank you! We have received your request and will reply within one month at the latest.',
        ],
        'delay_note' => 'We reply within one month at the latest. If we have reasonable doubts about your identity, we may ask you to confirm it.',
    ],

    'forms' => [
        'errors' => [
            'required' => 'This field is required.',
            'email' => 'Please enter a valid email address.',
            'too_long' => 'This text is too long.',
            'consent' => 'You must tick the consent box to submit the form.',
            'too_fast' => 'The form was submitted too quickly. Wait a few seconds, then try again.',
            'rate_limit' => 'Too many submissions from your connection. Please try again in an hour.',
            'csrf' => 'The form has expired. Reload the page, then try again.',
            'pdf_invalid' => 'The file must be a valid PDF.',
            'pdf_too_large' => 'The PDF exceeds the maximum size of 5 MB.',
            'image_invalid' => 'This file is not a valid image (JPEG, PNG or WebP).',
            'image_too_large' => 'The image exceeds the maximum allowed size.',
            'generic' => 'An error occurred. Please try again later.',
            'summary_title' => 'Errors to correct: :count',
        ],
        'required_legend' => 'Fields marked with an * are required.',
        'honeypot_label' => 'Leave this field empty',
        'choose' => 'Choose…',
    ],

    'footer' => [
        'about' => [
            'title' => 'Aéroboat France',
            'text' => 'Student association based in Toulouse: design, construction and testing of ground-effect aircraft models.',
        ],
        'nav' => [
            'title' => 'Navigation',
        ],
        'legal' => [
            'title' => 'Legal information',
            'legal' => 'Legal notice',
            'privacy' => 'Privacy',
            'cookies' => 'Cookies',
            'rights' => 'Exercise my rights',
        ],
        'contact' => [
            'title' => 'Contact',
            'text' => 'A question, or want to join or support us? Write to us.',
        ],
        'social' => [
            'title' => 'Follow us',
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
        ],
        'copyright' => '© :year Aéroboat France. All rights reserved.',
        'no_tracking' => 'No trackers, no cookies for visitors.',
    ],

    'legal' => [
        'title' => 'Legal notice',
        'lead' => 'Legal information about this website, in accordance with French law on trust in the digital economy.',
        'publisher' => [
            'title' => 'Site publisher',
            'name_label' => 'Name',
            'legal_form_label' => 'Legal form',
            'rna_label' => 'RNA number',
            'address_label' => 'Address',
            'city_label' => 'City',
            'email_label' => 'Email',
            'phone_label' => 'Phone',
            'director_label' => 'Publication director',
        ],
        'host' => [
            'title' => 'Hosting provider',
            'name_label' => 'Association',
            'address_label' => 'Address',
            'phone_label' => 'Phone',
        ],
        'ip' => [
            'title' => 'Intellectual property',
            'text' => 'The texts, images, logos, diagrams and visuals on this site are the property of Aéroboat France or of their respective authors and partners. Any reproduction or use without prior permission is prohibited unless stated otherwise. Partners’ trademarks and logos belong to their owners.',
        ],
        'liability' => [
            'title' => 'Liability',
            'text' => 'Aéroboat France strives to publish accurate and up-to-date information but does not guarantee the absence of errors or omissions. The V6 project is still being designed: the characteristics presented may change. Links to third-party sites are provided for information only; Aéroboat France is not responsible for their content.',
        ],
        'data' => [
            'title' => 'Personal data',
            'text' => 'The processing of personal data is detailed in the privacy policy. You can exercise your rights at any time.',
            'privacy_link' => 'Read the privacy policy',
            'rights_link' => 'Exercise my rights',
        ],
    ],

    'privacy' => [
        'title' => 'Privacy policy',
        'lead' => 'We collect only what is strictly necessary, and only when you write to us. We do not track your visits.',
        'controller' => [
            'title' => 'Data controller',
            'text' => 'The data controller is the association Aéroboat France, whose contact details appear below and in the legal notice.',
            'email_label' => 'Email',
            'phone_label' => 'Phone',
            'address_label' => 'Address',
        ],
        'forms' => [
            'title' => 'Data collected and purposes',
            'intro' => 'No personal data is collected when you simply browse the site. Data is recorded only if you fill in one of the forms below.',
            'join' => [
                'title' => '“Join us” form',
                'text' => 'First name, last name, email address, school or institution, level of study, skills, motivation, availability and, if you wish, your CV as a PDF file. Purpose: to review your application and reply to you.',
            ],
            'contact' => [
                'title' => '“Contact” form',
                'text' => 'Name, email address, subject and content of the message. Purpose: to reply to your message.',
            ],
            'rights' => [
                'title' => '“Exercise my rights” form',
                'text' => 'Name, email address, type and content of your request. Purpose: to handle your request to exercise your rights.',
            ],
            'members' => [
                'title' => 'Members’ area',
                'text' => 'Members’ area accounts are created by the association’s board. They include a username, a display name, possibly an email address, the date of the last login and a password stored only in hashed, non-reversible form. Purpose: to let authorised members manage the site.',
            ],
            'security' => [
                'title' => 'Limiting submissions and login attempts',
                'text' => 'To prevent abuse, the number of form submissions is limited, as is the number of login attempts to the members’ area. Your IP address is never stored in plain text: only an irreversible fingerprint, computed with a secret key, is recorded, for a few minutes up to 24 hours at most.',
            ],
        ],
        'basis' => [
            'title' => 'Legal basis',
            'text' => 'The application and contact forms rely on your consent, which you give by ticking the box provided. Handling requests to exercise rights meets a legal obligation. Limiting submissions and login attempts, as well as technical logs, rely on our legitimate interest in protecting the site against abuse.',
        ],
        'retention' => [
            'title' => 'Retention periods',
            'messages' => 'Contact messages and requests to exercise rights: 12 months.',
            'applications' => 'Applications: 24 months.',
            'cv' => 'CVs: kept as long as the corresponding application (24 months), in a folder to which access from the internet is forbidden.',
            'throttle' => 'IP address fingerprint used to limit submissions and login attempts: from a few minutes up to 24 hours at most.',
            'logs' => 'Technical logs of the site: 90 days at most.',
            'auto' => 'This deletion is automatic: a daily check, triggered by activity on the site (a form submission or opening the members’ area), deletes messages older than 12 months, applications older than 24 months (CVs included) and IP address fingerprints older than 24 hours. A purge button is also available in the members’ area.',
            'after' => 'You may ask for your data to be deleted before these dates.',
        ],
        'recipients' => [
            'title' => 'Recipients',
            'text' => 'Your data is intended for the board and team members in charge of handling your request. Data entered in the forms can be viewed only in the members’ area, which is reserved for authorised accounts. It is neither sold nor passed on to third parties. The association receives a notification email when a message or an application arrives, but this email contains no personal data: it only invites us to check the members’ area. The only processor is :host, which hosts the site and its database.',
        ],
        'hosting' => [
            'title' => 'Hosting and security',
            'generic_host' => 'our hosting provider',
            'text' => 'The site and its database are hosted by :host. Forms are protected against automated submissions without any tracker or cookie, and CVs are stored in a folder to which access from the internet is forbidden. Technical logs needed to operate and secure the service are kept; they contain no data entered by visitors and are retained for 90 days at most.',
        ],
        'tracking' => [
            'title' => 'No trackers',
            'text' => 'This site uses no audience measurement tool, no advertising, no embedded social network button and no external font or content service. No cookie is set for visitors. For more details, see the cookies page.',
            'cookies_link' => 'Cookies page',
        ],
        'rights' => [
            'title' => 'Your rights',
            'text_html' => 'Under the GDPR and the French Data Protection Act, you have the right of <strong>access</strong>, <strong>rectification</strong>, <strong>erasure</strong>, <strong>restriction</strong> and <strong>objection</strong>, as well as the right to <strong>portability</strong> of your data. Where processing is based on your consent, you may withdraw it at any time. We reply within one month at the latest.',
            'cta' => 'Exercise my rights using the dedicated form',
        ],
        'complaint' => [
            'title' => 'Complaints',
            'text_html' => 'If you believe your rights are not being respected, you may lodge a complaint with the French data protection authority (CNIL), 3 place de Fontenoy, TSA 80715, 75334 Paris Cedex 07, France, <a href="https://www.cnil.fr">www.cnil.fr</a>.',
        ],
        'contact' => [
            'title' => 'Contact us',
            'text' => 'For any question about your personal data, write to us at the address below or use the rights request form.',
        ],
    ],

    'cookies' => [
        'title' => 'Cookies',
        'lead' => 'No cookies for visitors; one strictly necessary session cookie is reserved for the members’ area.',
        'visitors' => [
            'title' => 'Site visitors',
            'text' => 'No cookie is placed on your device when you browse the site or fill in a form. Forms are protected by a signed token that does not rely on any cookie. We use no audience measurement or advertising tool, and no resource is loaded from an external service: fonts are hosted on the site itself.',
        ],
        'members' => [
            'title' => 'Members’ area',
            'text' => 'A strictly necessary session cookie is set as soon as the members’ area login page is opened, even before any username is entered, so that the login and its protection can work. It serves no other purpose and is never set when you browse the public pages of the site. This type of cookie is exempt from consent.',
            'name_label' => 'Cookie name',
            'name_value' => 'abf_admin',
            'purpose_label' => 'Purpose',
            'purpose_value' => 'Make the members’ area login work and keep the session',
            'duration_label' => 'Duration',
            'duration_value' => 'Session duration (12 hours at most)',
        ],
        'banner' => [
            'title' => 'No consent banner',
            'text' => 'Since no non-essential cookie is used, this site does not display a consent banner.',
        ],
        'more' => [
            'title' => 'Learn more',
            'text' => 'To learn more about how your data is processed, see the privacy policy.',
            'privacy_link' => 'Privacy policy',
        ],
    ],

    'errors' => [
        '404' => [
            'title' => 'Page not found',
            'text' => 'The requested page does not exist or has been moved.',
            'cta' => 'Back to home',
        ],
        '500' => [
            'title' => 'Server error',
            'text' => 'Something went wrong on our side. Please try again in a few moments.',
        ],
        '403' => [
            'title' => 'Access denied',
            'text' => 'You are not allowed to access this page.',
        ],
    ],

    'admin_public' => [
        'restricted' => 'Area reserved for association members.',
        'link' => 'Members’ area',
    ],

    'img' => [
        'hero-v6' => 'Computer-generated image of a blue ground-effect model skimming a lake, with misty mountains in the background',
        'lake-alpine' => 'Computer-generated image of a white ground-effect model over a mountain lake, with peaks in the background',
        'water-run' => 'Red propeller-driven model floating on a lake, seen from three-quarters',
        'water-skim' => 'Red model racing across the water, throwing up spray',
        'hold-red' => 'A person holds a large red model at arm’s length in a park',
        'lakeside-duo' => 'Two people carry a red model beside a lake',
        'ribs-ground' => 'Exposed ribs of a wing under construction, laid on paving, with red ends',
        'dusk-ground' => 'Ribbed structure of a model on the ground at dusk, surrounded by trees',
        'dusk-lake' => 'Dark model on a lake at dusk, with a kayaker in silhouette in the background',
        'bench-build' => 'Workshop: a red fuselage on a workbench with wiring, a propeller and a battery safety bag',
        'wing-closeup' => 'Close-up of the wooden structure of a wing lying on the ground, with trees in the background',
        'wing-pavement' => 'Wing with exposed ribs lying on paving, with red fins at the tips',
        'cfd-streams' => 'Numerical simulation: coloured airstreams, from blue to yellow, around a wing shape',
        'cfd-color' => 'Numerical simulation of a T-tail model with its surface coloured from blue to green',
        'render-dark' => '3D render on a dark background of a red and wooden model with ribbed wings and a T-tail',
        'render-white' => '3D render of a white low-wing model with a T-tail and two propellers',
        'render-blue' => '3D render of a white model with a blue low wing, a T-tail and propellers',
        'team-build' => 'Two people, crouching on paving, assembling a model with a blue covering',
        'cgi-v-red' => 'Computer-generated image of a red aircraft with long wooden wings, a T-tail and a glazed cockpit',
        'model-water' => 'Red propeller-driven model gliding on the water, seen head-on just above the surface',
        'ribs-lawn' => 'Large model with a wooden wing and red fins lying on paving beside a lawn',
        'event-stand' => 'Three people at a trade-show stand, with a blue model suspended in front of them',
        'team-clement' => 'Portrait of Clément Thibault',
        'team-joan' => 'Portrait of Joan Le Pouhaër',
        'team-pierre' => 'Portrait of Pierre Stamm',
        'team-kerian' => 'Portrait of Kerian Fiter',
        'team-mathis' => 'Portrait of Mathis Wojtkowski',
        'team-benjamin' => 'Portrait of Benjamin Canovas',
        'team-chislain' => 'Portrait of Chislain Jaures Kamdem Kam',
        'team-robin' => 'Portrait of Robin Canovas',
        'logo' => 'Aéroboat France logo: a circle bearing the name, with the silhouette of a T-tail aircraft between two swirls, one blue and one red',
        'logo-white' => 'Aéroboat France logo in white: a circle bearing the name, with the silhouette of a T-tail aircraft between two swirls, one blue and one red',
        'partner-3dx' => '3DEXPERIENCE logo',
        'partner-ionis' => 'IONIS Education Group logo',
        'partner-uncb' => 'Logo of the association “Un morceau de ciel bleu”: an artificial horizon dial with an aircraft in flight',
    ],
];
