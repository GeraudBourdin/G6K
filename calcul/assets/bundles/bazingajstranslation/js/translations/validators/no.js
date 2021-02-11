(function (t) {
// no
t.add("This value should be false.", "Verdien m\u00e5 v\u00e6re usann.", "validators", "no");
t.add("This value should be true.", "Verdien m\u00e5 v\u00e6re sann.", "validators", "no");
t.add("This value should be of type {{ type }}.", "Verdien skal ha typen {{ type }}.", "validators", "no");
t.add("This value should be blank.", "Verdien skal v\u00e6re blank.", "validators", "no");
t.add("The value you selected is not a valid choice.", "Den valgte verdien er ikke gyldig.", "validators", "no");
t.add("You must select at least {{ limit }} choice.|You must select at least {{ limit }} choices.", "Du m\u00e5 velge minst {{ limit }} valg.", "validators", "no");
t.add("You must select at most {{ limit }} choice.|You must select at most {{ limit }} choices.", "Du kan maks velge {{ limit }} valg.", "validators", "no");
t.add("One or more of the given values is invalid.", "En eller flere av de oppgitte verdiene er ugyldige.", "validators", "no");
t.add("This field was not expected.", "Dette feltet var ikke forventet.", "validators", "no");
t.add("This field is missing.", "Dette feltet mangler.", "validators", "no");
t.add("This value is not a valid date.", "Verdien er ikke en gyldig dato.", "validators", "no");
t.add("This value is not a valid datetime.", "Verdien er ikke en gyldig dato\/tid.", "validators", "no");
t.add("This value is not a valid email address.", "Verdien er ikke en gyldig e-postadresse.", "validators", "no");
t.add("The file could not be found.", "Filen kunne ikke finnes.", "validators", "no");
t.add("The file is not readable.", "Filen er ikke lesbar.", "validators", "no");
t.add("The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.", "Filen er for stor ({{ size }} {{ suffix }}). Tilatte maksimale st\u00f8rrelse {{ limit }} {{ suffix }}.", "validators", "no");
t.add("The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.", "Mimetypen av filen er ugyldig ({{ type }}). Tilatte mimetyper er {{ types }}.", "validators", "no");
t.add("This value should be {{ limit }} or less.", "Verdien m\u00e5 v\u00e6re {{ limit }} tegn lang eller mindre.", "validators", "no");
t.add("This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.", "Verdien er for lang. Den m\u00e5 ha {{ limit }} tegn eller mindre.", "validators", "no");
t.add("This value should be {{ limit }} or more.", "Verdien m\u00e5 v\u00e6re {{ limit }} eller mer.", "validators", "no");
t.add("This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.", "Verdien er for kort. Den m\u00e5 ha {{ limit }} tegn eller flere.", "validators", "no");
t.add("This value should not be blank.", "Verdien kan ikke v\u00e6re blank.", "validators", "no");
t.add("This value should not be null.", "Verdien kan ikke v\u00e6re tom (null).", "validators", "no");
t.add("This value should be null.", "Verdien skal v\u00e6re tom (null).", "validators", "no");
t.add("This value is not valid.", "Verdien er ugyldig.", "validators", "no");
t.add("This value is not a valid time.", "Verdien er ikke en gyldig tid.", "validators", "no");
t.add("This value is not a valid URL.", "Verdien er ikke en gyldig URL.", "validators", "no");
t.add("The two values should be equal.", "Verdiene skal v\u00e6re identiske.", "validators", "no");
t.add("The file is too large. Allowed maximum size is {{ limit }} {{ suffix }}.", "Filen er for stor. Den maksimale st\u00f8rrelsen er {{ limit }} {{ suffix }}.", "validators", "no");
t.add("The file is too large.", "Filen er for stor.", "validators", "no");
t.add("The file could not be uploaded.", "Filen kunne ikke lastes opp.", "validators", "no");
t.add("This value should be a valid number.", "Verdien skal v\u00e6re et gyldig tall.", "validators", "no");
t.add("This file is not a valid image.", "Denne filen er ikke et gyldig bilde.", "validators", "no");
t.add("This is not a valid IP address.", "Dette er ikke en gyldig IP adresse.", "validators", "no");
t.add("This value is not a valid language.", "Verdien er ikke et gyldig spr\u00e5k.", "validators", "no");
t.add("This value is not a valid locale.", "Verdien er ikke en gyldig lokalitet.", "validators", "no");
t.add("This value is not a valid country.", "Verdien er ikke et gyldig navn p\u00e5 land.", "validators", "no");
t.add("This value is already used.", "Verdien er allerede brukt.", "validators", "no");
t.add("The size of the image could not be detected.", "Bildest\u00f8rrelsen kunne ikke oppdages.", "validators", "no");
t.add("The image width is too big ({{ width }}px). Allowed maximum width is {{ max_width }}px.", "Bildebredden er for stor ({{ width }} piksler). Tillatt maksimumsbredde er {{ max_width }} piksler.", "validators", "no");
t.add("The image width is too small ({{ width }}px). Minimum width expected is {{ min_width }}px.", "Bildebredden er for liten ({{ width }} piksler). Forventet minimumsbredde er {{ min_width }} piksler.", "validators", "no");
t.add("The image height is too big ({{ height }}px). Allowed maximum height is {{ max_height }}px.", "Bildeh\u00f8yden er for stor ({{ height }} piksler). Tillatt maksimumsh\u00f8yde er {{ max_height }} piksler.", "validators", "no");
t.add("The image height is too small ({{ height }}px). Minimum height expected is {{ min_height }}px.", "Bildeh\u00f8yden er for liten ({{ height }} piksler). Forventet minimumsh\u00f8yde er {{ min_height }} piksler.", "validators", "no");
t.add("This value should be the user's current password.", "Verdien skal v\u00e6re brukerens sitt n\u00e5v\u00e6rende passord.", "validators", "no");
t.add("This value should have exactly {{ limit }} character.|This value should have exactly {{ limit }} characters.", "Verdien skal v\u00e6re n\u00f8yaktig {{ limit }} tegn.", "validators", "no");
t.add("The file was only partially uploaded.", "Filen var kun delvis opplastet.", "validators", "no");
t.add("No file was uploaded.", "Ingen fil var lastet opp.", "validators", "no");
t.add("No temporary folder was configured in php.ini.", "Den midlertidige mappen (tmp) er ikke konfigurert i php.ini.", "validators", "no");
t.add("Cannot write temporary file to disk.", "Kan ikke skrive midlertidig fil til disk.", "validators", "no");
t.add("A PHP extension caused the upload to fail.", "En PHP-utvidelse for\u00e5rsaket en feil under opplasting.", "validators", "no");
t.add("This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.", "Denne samlingen m\u00e5 inneholde {{ limit }} element eller flere.|Denne samlingen m\u00e5 inneholde {{ limit }} elementer eller flere.", "validators", "no");
t.add("This collection should contain {{ limit }} element or less.|This collection should contain {{ limit }} elements or less.", "Denne samlingen m\u00e5 inneholde {{ limit }} element eller f\u00e6rre.|Denne samlingen m\u00e5 inneholde {{ limit }} elementer eller f\u00e6rre.", "validators", "no");
t.add("This collection should contain exactly {{ limit }} element.|This collection should contain exactly {{ limit }} elements.", "Denne samlingen m\u00e5 inneholde n\u00f8yaktig {{ limit }} element.|Denne samlingen m\u00e5 inneholde n\u00f8yaktig {{ limit }} elementer.", "validators", "no");
t.add("Invalid card number.", "Ugyldig kortnummer.", "validators", "no");
t.add("Unsupported card type or invalid card number.", "Korttypen er ikke st\u00f8ttet eller kortnummeret er ugyldig.", "validators", "no");
t.add("This is not a valid International Bank Account Number (IBAN).", "Dette er ikke et gyldig IBAN-nummer.", "validators", "no");
t.add("This value is not a valid ISBN-10.", "Verdien er ikke en gyldig ISBN-10.", "validators", "no");
t.add("This value is not a valid ISBN-13.", "Verdien er ikke en gyldig ISBN-13.", "validators", "no");
t.add("This value is neither a valid ISBN-10 nor a valid ISBN-13.", "Verdien er hverken en gyldig ISBN-10 eller ISBN-13.", "validators", "no");
t.add("This value is not a valid ISSN.", "Verdien er ikke en gyldig ISSN.", "validators", "no");
t.add("This value is not a valid currency.", "Verdien er ikke gyldig valuta.", "validators", "no");
t.add("This value should be equal to {{ compared_value }}.", "Verdien skal v\u00e6re lik {{ compared_value }}.", "validators", "no");
t.add("This value should be greater than {{ compared_value }}.", "Verdien skal v\u00e6re st\u00f8rre enn {{ compared_value }}.", "validators", "no");
t.add("This value should be greater than or equal to {{ compared_value }}.", "Verdien skal v\u00e6re st\u00f8rre enn eller lik {{ compared_value }}.", "validators", "no");
t.add("This value should be identical to {{ compared_value_type }} {{ compared_value }}.", "Verdien skal v\u00e6re identisk med {{ compared_value_type }} {{ compared_value }}.", "validators", "no");
t.add("This value should be less than {{ compared_value }}.", "Verdien skal v\u00e6re mindre enn {{ compared_value }}.", "validators", "no");
t.add("This value should be less than or equal to {{ compared_value }}.", "Verdien skal v\u00e6re mindre enn eller lik {{ compared_value }}.", "validators", "no");
t.add("This value should not be equal to {{ compared_value }}.", "Verdien skal ikke v\u00e6re lik {{ compared_value }}.", "validators", "no");
t.add("This value should not be identical to {{ compared_value_type }} {{ compared_value }}.", "Verdien skal ikke v\u00e6re identisk med {{ compared_value_type }} {{ compared_value }}.", "validators", "no");
t.add("The image ratio is too big ({{ ratio }}). Allowed maximum ratio is {{ max_ratio }}.", "Bildeforholdet er for stort ({{ ratio }}). Tillatt bildeforhold er maks {{ max_ratio }}.", "validators", "no");
t.add("The image ratio is too small ({{ ratio }}). Minimum ratio expected is {{ min_ratio }}.", "Bildeforholdet er for lite ({{ ratio }}). Forventet bildeforhold er minst {{ min_ratio }}.", "validators", "no");
t.add("The image is square ({{ width }}x{{ height }}px). Square images are not allowed.", "Bildet er en kvadrat ({{ width }}x{{ height }}px). Kvadratiske bilder er ikke tillatt.", "validators", "no");
t.add("The image is landscape oriented ({{ width }}x{{ height }}px). Landscape oriented images are not allowed.", "Bildet er i liggende retning ({{ width }}x{{ height }}px). Bilder i liggende retning er ikke tillatt.", "validators", "no");
t.add("The image is portrait oriented ({{ width }}x{{ height }}px). Portrait oriented images are not allowed.", "Bildet er i st\u00e5ende retning ({{ width }}x{{ height }}px). Bilder i st\u00e5ende retning er ikke tillatt.", "validators", "no");
t.add("An empty file is not allowed.", "Tomme filer er ikke tilatt.", "validators", "no");
t.add("The host could not be resolved.", "Vertsnavn kunne ikke l\u00f8ses.", "validators", "no");
t.add("This value does not match the expected {{ charset }} charset.", "Verdien samsvarer ikke med forventet tegnsett {{ charset }}.", "validators", "no");
t.add("This is not a valid Business Identifier Code (BIC).", "Dette er ikke en gyldig BIC.", "validators", "no");
t.add("Error", "Feil", "validators", "no");
t.add("This is not a valid UUID.", "Dette er ikke en gyldig UUID.", "validators", "no");
t.add("This value should be a multiple of {{ compared_value }}.", "Verdien skal v\u00e6re flertall av {{ compared_value }}.", "validators", "no");
t.add("This Business Identifier Code (BIC) is not associated with IBAN {{ iban }}.", "Business Identifier Code (BIC) er ikke tilknyttet en IBAN {{ iban }}.", "validators", "no");
t.add("This value should be valid JSON.", "Verdien er ikke gyldig JSON.", "validators", "no");
t.add("This collection should contain only unique elements.", "Samlingen kan kun inneholde unike elementer.", "validators", "no");
t.add("This value should be positive.", "Denne verdien m\u00e5 v\u00e6re positiv.", "validators", "no");
t.add("This value should be either positive or zero.", "Denne verdien m\u00e5 v\u00e6re positiv eller null.", "validators", "no");
t.add("This value should be negative.", "Denne verdien m\u00e5 v\u00e6re negativ.", "validators", "no");
t.add("This value should be either negative or zero.", "Denne verdien m\u00e5 v\u00e6re negativ eller null.", "validators", "no");
t.add("This value is not a valid timezone.", "Verdien er ikke en gyldig tidssone.", "validators", "no");
t.add("This password has been leaked in a data breach, it must not be used. Please use another password.", "Dette passordet er lekket i et datainnbrudd, det m\u00e5 ikke tas i bruk. Vennligst bruk et annet passord.", "validators", "no");
t.add("This value should be between {{ min }} and {{ max }}.", "Verdien m\u00e5 v\u00e6re mellom {{ min }} og {{ max }}.", "validators", "no");
t.add("This value is not a valid hostname.", "Denne verdien er ikke et gyldig vertsnavn.", "validators", "no");
t.add("The number of elements in this collection should be a multiple of {{ compared_value }}.", "Antall elementer i denne samlingen b\u00f8r v\u00e6re et multiplum av {{ compared_value }}.", "validators", "no");
t.add("This value should satisfy at least one of the following constraints:", "Denne verdien skal tilfredsstille minst en av f\u00f8lgende begrensninger:", "validators", "no");
t.add("Each element of this collection should satisfy its own set of constraints.", "Hvert element i denne samlingen skal tilfredsstille sitt eget sett med begrensninger.", "validators", "no");
t.add("This value is not a valid International Securities Identification Number (ISIN).", "Denne verdien er ikke et gyldig International Securities Identification Number (ISIN).", "validators", "no");
})(Translator);
