<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pays')->delete();
        $countries = array(
            array('id' => 1,'code' => 'AF' ,'name' => "Afghanistan",'phonecode' => 93,'published' => 0),
            array('id' => 2,'code' => 'AL' ,'name' => "Albania",'phonecode' => 355,'published' => 0),
            array('id' => 3,'code' => 'DZ' ,'name' => "Algeria",'phonecode' => 213,'published' => 0),
            array('id' => 4,'code' => 'AS' ,'name' => "American Samoa",'phonecode' => 1684,'published' => 0),
            array('id' => 5,'code' => 'AD' ,'name' => "Andorra",'phonecode' => 376,'published' => 0),
            array('id' => 6,'code' => 'AO' ,'name' => "Angola",'phonecode' => 244,'published' => 1),
            array('id' => 7,'code' => 'AI' ,'name' => "Anguilla",'phonecode' => 1264,'published' => 0),
            array('id' => 8,'code' => 'AQ' ,'name' => "Antarctica",'phonecode' => 0,'published' => 0),
            array('id' => 9,'code' => 'AG' ,'name' => "Antigua And Barbuda",'phonecode' => 1268,'published' => 0),
            array('id' => 10,'code' => 'AR','name' => "Argentina",'phonecode' => 54,'published' => 0),
            array('id' => 11,'code' => 'AM','name' => "Armenia",'phonecode' => 374,'published' => 0),
            array('id' => 12,'code' => 'AW','name' => "Aruba",'phonecode' => 297,'published' => 0),
            array('id' => 13,'code' => 'AU','name' => "Australia",'phonecode' => 61,'published' => 0),
            array('id' => 14,'code' => 'AT','name' => "Austria",'phonecode' => 43,'published' => 0),
            array('id' => 15,'code' => 'AZ','name' => "Azerbaijan",'phonecode' => 994,'published' => 0),
            array('id' => 16,'code' => 'BS','name' => "Bahamas The",'phonecode' => 1242,'published' => 0),
            array('id' => 17,'code' => 'BH','name' => "Bahrain",'phonecode' => 973,'published' => 0),
            array('id' => 18,'code' => 'BD','name' => "Bangladesh",'phonecode' => 880,'published' => 0),
            array('id' => 19,'code' => 'BB','name' => "Barbados",'phonecode' => 1246,'published' => 0),
            array('id' => 20,'code' => 'BY','name' => "Belarus",'phonecode' => 375,'published' => 0),
            array('id' => 21,'code' => 'BE','name' => "Belgium",'phonecode' => 32,'published' => 0),
            array('id' => 22,'code' => 'BZ','name' => "Belize",'phonecode' => 501,'published' => 0),
            array('id' => 23,'code' => 'BJ','name' => "Benin",'phonecode' => 229,'published' => 1),
            array('id' => 24,'code' => 'BM','name' => "Bermuda",'phonecode' => 1441,'published' => 0),
            array('id' => 25,'code' => 'BT','name' => "Bhutan",'phonecode' => 975,'published' => 0),
            array('id' => 26,'code' => 'BO','name' => "Bolivia",'phonecode' => 591,'published' => 0),
            array('id' => 27,'code' => 'BA','name' => "Bosnia and Herzegovina",'phonecode' => 387,'published' => 0),
            array('id' => 28,'code' => 'BW','name' => "Botswana",'phonecode' => 267,'published' => 0),
            array('id' => 29,'code' => 'BV','name' => "Bouvet Island",'phonecode' => 0,'published' => 0),
            array('id' => 30,'code' => 'BR','name' => "Brazil",'phonecode' => 55,'published' => 0),
            array('id' => 31,'code' => 'IO','name' => "British Indian Ocean Territory",'phonecode' => 246,'published' => 0),
            array('id' => 32,'code' => 'BN','name' => "Brunei",'phonecode' => 673,'published' => 0),
            array('id' => 33,'code' => 'BG','name' => "Bulgaria",'phonecode' => 359,'published' => 0),
            array('id' => 34,'code' => 'BF','name' => "Burkina Faso",'phonecode' => 226,'published' => 1),
            array('id' => 35,'code' => 'BI','name' => "Burundi",'phonecode' => 257,'published' => 1),
            array('id' => 36,'code' => 'KH','name' => "Cambodia",'phonecode' => 855,'published' => 0),
            array('id' => 37,'code' => 'CM','name' => "Cameroon",'phonecode' => 237,'published' => 1),
            array('id' => 38,'code' => 'CA','name' => "Canada",'phonecode' => 1,'published' => 0),
            array('id' => 39,'code' => 'CV','name' => "Cape Verde",'phonecode' => 238,'published' => 1),
            array('id' => 40,'code' => 'KY','name' => "Cayman Islands",'phonecode' => 1345,'published' => 0),
            array('id' => 41,'code' => 'CF','name' => "Central African Republic",'phonecode' => 236,'published' => 1),
            array('id' => 42,'code' => 'TD','name' => "TChad",'phonecode' => 235,'published' => 1),
            array('id' => 43,'code' => 'CL','name' => "Chile",'phonecode' => 56,'published' => 0),
            array('id' => 44,'code' => 'CN','name' => "China",'phonecode' => 86,'published' => 0),
            array('id' => 45,'code' => 'CX','name' => "Christmas Island",'phonecode' => 61,'published' => 0),
            array('id' => 46,'code' => 'CC','name' => "Cocos (Keeling) Islands",'phonecode' => 672,'published' => 0),
            array('id' => 47,'code' => 'CO','name' => "Colombia",'phonecode' => 57,'published' => 0),
            array('id' => 48,'code' => 'KM','name' => "Comoros",'phonecode' => 269,'published' => 0),
            array('id' => 49,'code' => 'CG','name' => "Congo",'phonecode' => 242,'published' => 1),
            array('id' => 50,'code' => 'CD','name' => "Congo The Democratic Republic Of The",'phonecode' => 242,'published' => 1),
            array('id' => 51,'code' => 'CK','name' => "Cook Islands",'phonecode' => 682,'published' => 0),
            array('id' => 52,'code' => 'CR','name' => "Costa Rica",'phonecode' => 506,'published' => 0),
            array('id' => 53,'code' => 'CI','name' => "Cote D Ivoire (Ivory Coast)",'phonecode' => 225,'published' => 1),
            array('id' => 54,'code' => 'HR','name' => "Croatia (Hrvatska)",'phonecode' => 385,'published' => 0),
            array('id' => 55,'code' => 'CU','name' => "Cuba",'phonecode' => 53,'published' => 0),
            array('id' => 56,'code' => 'CY','name' => "Cyprus",'phonecode' => 357,'published' => 0),
            array('id' => 57,'code' => 'CZ','name' => "Czech Republic",'phonecode' => 420,'published' => 0),
            array('id' => 58,'code' => 'DK','name' => "Denmark",'phonecode' => 45,'published' => 0),
            array('id' => 59,'code' => 'DJ','name' => "Djibouti",'phonecode' => 253,'published' => 1),
            array('id' => 60,'code' => 'DM','name' => "Dominica",'phonecode' => 1767,'published' => 0),
            array('id' => 61,'code' => 'DO','name' => "Dominican Republic",'phonecode' => 1809,'published' => 0),
            array('id' => 62,'code' => 'TP','name' => "East Timor",'phonecode' => 670,'published' => 0),
            array('id' => 63,'code' => 'EC','name' => "Ecuador",'phonecode' => 593,'published' => 0),
            array('id' => 64,'code' => 'EG','name' => "Egypt",'phonecode' => 20,'published' => 0),
            array('id' => 65,'code' => 'SV','name' => "El Salvador",'phonecode' => 503,'published' => 0),
            array('id' => 66,'code' => 'GQ','name' => "Equatorial Guinea",'phonecode' => 240,'published' => 1),
            array('id' => 67,'code' => 'ER','name' => "Eritrea",'phonecode' => 291,'published' => 1),
            array('id' => 68,'code' => 'EE','name' => "Estonia",'phonecode' => 372,'published' => 0),
            array('id' => 69,'code' => 'ET','name' => "Ethiopia",'phonecode' => 251,'published' => 1),
            array('id' => 70,'code' => 'XA','name' => "External Territories of Australia",'phonecode' => 61,'published' => 0),
            array('id' => 71,'code' => 'FK','name' => "Falkland Islands",'phonecode' => 500,'published' => 0),
            array('id' => 72,'code' => 'FO','name' => "Faroe Islands",'phonecode' => 298,'published' => 0),
            array('id' => 73,'code' => 'FJ','name' => "Fiji Islands",'phonecode' => 679,'published' => 0),
            array('id' => 74,'code' => 'FI','name' => "Finland",'phonecode' => 358,'published' => 0),
            array('id' => 75,'code' => 'FR','name' => "France",'phonecode' => 33,'published' => 0),
            array('id' => 76,'code' => 'GF','name' => "French Guiana",'phonecode' => 594,'published' => 0),
            array('id' => 77,'code' => 'PF','name' => "French Polynesia",'phonecode' => 689,'published' => 0),
            array('id' => 78,'code' => 'TF','name' => "French Southern Territories",'phonecode' => 0,'published' => 0),
            array('id' => 79,'code' => 'GA','name' => "Gabon",'phonecode' => 241,'published' => 1),
            array('id' => 80,'code' => 'GM','name' => "Gambia The",'phonecode' => 220,'published' => 1),
            array('id' => 81,'code' => 'GE','name' => "Georgia",'phonecode' => 995,'published' => 0),
            array('id' => 82,'code' => 'DE','name' => "Germany",'phonecode' => 49,'published' => 0),
            array('id' => 83,'code' => 'GH','name' => "Ghana",'phonecode' => 233,'published' => 1),
            array('id' => 84,'code' => 'GI','name' => "Gibraltar",'phonecode' => 350,'published' => 0),
            array('id' => 85,'code' => 'GR','name' => "Greece",'phonecode' => 30,'published' => 0),
            array('id' => 86,'code' => 'GL','name' => "Greenland",'phonecode' => 299,'published' => 0),
            array('id' => 87,'code' => 'GD','name' => "Grenada",'phonecode' => 1473,'published' => 0),
            array('id' => 88,'code' => 'GP','name' => "Guadeloupe",'phonecode' => 590,'published' => 0),
            array('id' => 89,'code' => 'GU','name' => "Guam",'phonecode' => 1671,'published' => 0),
            array('id' => 90,'code' => 'GT','name' => "Guatemala",'phonecode' => 502,'published' => 0),
            array('id' => 91,'code' => 'XU','name' => "Guernsey and Alderney",'phonecode' => 44,'published' => 0),
            array('id' => 92,'code' => 'GN','name' => "Guinea",'phonecode' => 224,'published' => 1),
            array('id' => 93,'code' => 'GW','name' => "Guinea-Bissau",'phonecode' => 245,'published' => 1),
            array('id' => 94,'code' => 'GY','name' => "Guyana",'phonecode' => 592,'published' => 0),
            array('id' => 95,'code' => 'HT','name' => "Haiti",'phonecode' => 509,'published' => 0),
            array('id' => 96,'code' => 'HM','name' => "Heard and McDonald Islands",'phonecode' => 0,'published' => 0),
            array('id' => 97,'code' => 'HN','name' => "Honduras",'phonecode' => 504,'published' => 0),
            array('id' => 98,'code' => 'HK','name' => "Hong Kong S.A.R.",'phonecode' => 852,'published' => 0),
            array('id' => 99,'code' => 'HU','name' => "Hungary",'phonecode' => 36,'published' => 0),
            array('id' => 100,'code' => 'IS','name' => "Iceland",'phonecode' => 354,'published' => 0),
            array('id' => 101,'code' => 'IN','name' => "India",'phonecode' => 91,'published' => 0),
            array('id' => 102,'code' => 'ID','name' => "Indonesia",'phonecode' => 62,'published' => 0),
            array('id' => 103,'code' => 'IR','name' => "Iran",'phonecode' => 98,'published' => 0),
            array('id' => 104,'code' => 'IQ','name' => "Iraq",'phonecode' => 964,'published' => 0),
            array('id' => 105,'code' => 'IE','name' => "Ireland",'phonecode' => 353,'published' => 0),
            array('id' => 106,'code' => 'IL','name' => "Israel",'phonecode' => 972,'published' => 0),
            array('id' => 107,'code' => 'IT','name' => "Italy",'phonecode' => 39,'published' => 0),
            array('id' => 108,'code' => 'JM','name' => "Jamaica",'phonecode' => 1876,'published' => 0),
            array('id' => 109,'code' => 'JP','name' => "Japan",'phonecode' => 81,'published' => 0),
            array('id' => 110,'code' => 'XJ','name' => "Jersey",'phonecode' => 44,'published' => 0),
            array('id' => 111,'code' => 'JO','name' => "Jordan",'phonecode' => 962,'published' => 0),
            array('id' => 112,'code' => 'KZ','name' => "Kazakhstan",'phonecode' => 7,'published' => 0),
            array('id' => 113,'code' => 'KE','name' => "Kenya",'phonecode' => 254,'published' => 1),
            array('id' => 114,'code' => 'KI','name' => "Kiribati",'phonecode' => 686,'published' => 0),
            array('id' => 115,'code' => 'KP','name' => "Korea North",'phonecode' => 850,'published' => 0),
            array('id' => 116,'code' => 'KR','name' => "Korea South",'phonecode' => 82,'published' => 0),
            array('id' => 117,'code' => 'KW','name' => "Kuwait",'phonecode' => 965,'published' => 0),
            array('id' => 118,'code' => 'KG','name' => "Kyrgyzstan",'phonecode' => 996,'published' => 0),
            array('id' => 119,'code' => 'LA','name' => "Laos",'phonecode' => 856,'published' => 0),
            array('id' => 120,'code' => 'LV','name' => "Latvia",'phonecode' => 371,'published' => 0),
            array('id' => 121,'code' => 'LB','name' => "Lebanon",'phonecode' => 961,'published' => 0),
            array('id' => 122,'code' => 'LS','name' => "Lesotho",'phonecode' => 266,'published' => 0),
            array('id' => 123,'code' => 'LR','name' => "Liberia",'phonecode' => 231,'published' => 1),
            array('id' => 124,'code' => 'LY','name' => "Libya",'phonecode' => 218,'published' => 0),
            array('id' => 125,'code' => 'LI','name' => "Liechtenstein",'phonecode' => 423,'published' => 0),
            array('id' => 126,'code' => 'LT','name' => "Lithuania",'phonecode' => 370,'published' => 0),
            array('id' => 127,'code' => 'LU','name' => "Luxembourg",'phonecode' => 352,'published' => 0),
            array('id' => 128,'code' => 'MO','name' => "Macau S.A.R.",'phonecode' => 853,'published' => 0),
            array('id' => 129,'code' => 'MK','name' => "Macedonia",'phonecode' => 389,'published' => 0),
            array('id' => 130,'code' => 'MG','name' => "Madagascar",'phonecode' => 261,'published' => 0),
            array('id' => 131,'code' => 'MW','name' => "Malawi",'phonecode' => 265,'published' => 0),
            array('id' => 132,'code' => 'MY','name' => "Malaysia",'phonecode' => 60,'published' => 0),
            array('id' => 133,'code' => 'MV','name' => "Maldives",'phonecode' => 960,'published' => 0),
            array('id' => 134,'code' => 'ML','name' => "Mali",'phonecode' => 223,'published' => 1),
            array('id' => 135,'code' => 'MT','name' => "Malta",'phonecode' => 356,'published' => 0),
            array('id' => 136,'code' => 'XM','name' => "Man (Isle of)",'phonecode' => 44,'published' => 0),
            array('id' => 137,'code' => 'MH','name' => "Marshall Islands",'phonecode' => 692,'published' => 0),
            array('id' => 138,'code' => 'MQ','name' => "Martinique",'phonecode' => 596,'published' => 0),
            array('id' => 139,'code' => 'MR','name' => "Mauritania",'phonecode' => 222,'published' => 1),
            array('id' => 140,'code' => 'MU','name' => "Mauritius",'phonecode' => 230,'published' => 0),
            array('id' => 141,'code' => 'YT','name' => "Mayotte",'phonecode' => 269,'published' => 0),
            array('id' => 142,'code' => 'MX','name' => "Mexico",'phonecode' => 52,'published' => 0),
            array('id' => 143,'code' => 'FM','name' => "Micronesia",'phonecode' => 691,'published' => 0),
            array('id' => 144,'code' => 'MD','name' => "Moldova",'phonecode' => 373,'published' => 0),
            array('id' => 145,'code' => 'MC','name' => "Monaco",'phonecode' => 377,'published' => 0),
            array('id' => 146,'code' => 'MN','name' => "Mongolia",'phonecode' => 976,'published' => 0),
            array('id' => 147,'code' => 'MS','name' => "Montserrat",'phonecode' => 1664,'published' => 0),
            array('id' => 148,'code' => 'MA','name' => "Morocco",'phonecode' => 212,'published' => 0),
            array('id' => 149,'code' => 'MZ','name' => "Mozambique",'phonecode' => 258,'published' => 0),
            array('id' => 150,'code' => 'MM','name' => "Myanmar",'phonecode' => 95,'published' => 0),
            array('id' => 151,'code' => 'NA','name' => "Namibia",'phonecode' => 264,'published' => 0),
            array('id' => 152,'code' => 'NR','name' => "Nauru",'phonecode' => 674,'published' => 0),
            array('id' => 153,'code' => 'NP','name' => "Nepal",'phonecode' => 977,'published' => 0),
            array('id' => 154,'code' => 'AN','name' => "Netherlands Antilles",'phonecode' => 599,'published' => 0),
            array('id' => 155,'code' => 'NL','name' => "Netherlands The",'phonecode' => 31,'published' => 0),
            array('id' => 156,'code' => 'NC','name' => "New Caledonia",'phonecode' => 687,'published' => 0),
            array('id' => 157,'code' => 'NZ','name' => "New Zealand",'phonecode' => 64,'published' => 0),
            array('id' => 158,'code' => 'NI','name' => "Nicaragua",'phonecode' => 505,'published' => 0),
            array('id' => 159,'code' => 'NE','name' => "Niger",'phonecode' => 227,'published' => 1),
            array('id' => 160,'code' => 'NG','name' => "Nigeria",'phonecode' => 234,'published' => 1),
            array('id' => 161,'code' => 'NU','name' => "Niue",'phonecode' => 683,'published' => 0),
            array('id' => 162,'code' => 'NF','name' => "Norfolk Island",'phonecode' => 672,'published' => 0),
            array('id' => 163,'code' => 'MP','name' => "Northern Mariana Islands",'phonecode' => 1670,'published' => 0),
            array('id' => 164,'code' => 'NO','name' => "Norway",'phonecode' => 47,'published' => 0),
            array('id' => 165,'code' => 'OM','name' => "Oman",'phonecode' => 968,'published' => 0),
            array('id' => 166,'code' => 'PK','name' => "Pakistan",'phonecode' => 92,'published' => 0),
            array('id' => 167,'code' => 'PW','name' => "Palau",'phonecode' => 680,'published' => 0),
            array('id' => 168,'code' => 'PS','name' => "Palestinian Territory Occupied",'phonecode' => 970,'published' => 0),
            array('id' => 169,'code' => 'PA','name' => "Panama",'phonecode' => 507,'published' => 0),
            array('id' => 170,'code' => 'PG','name' => "Papua new Guinea",'phonecode' => 675,'published' => 0),
            array('id' => 171,'code' => 'PY','name' => "Paraguay",'phonecode' => 595,'published' => 0),
            array('id' => 172,'code' => 'PE','name' => "Peru",'phonecode' => 51,'published' => 0),
            array('id' => 173,'code' => 'PH','name' => "Philippines",'phonecode' => 63,'published' => 0),
            array('id' => 174,'code' => 'PN','name' => "Pitcairn Island",'phonecode' => 0,'published' => 0),
            array('id' => 175,'code' => 'PL','name' => "Poland",'phonecode' => 48,'published' => 0),
            array('id' => 176,'code' => 'PT','name' => "Portugal",'phonecode' => 351,'published' => 0),
            array('id' => 177,'code' => 'PR','name' => "Puerto Rico",'phonecode' => 1787,'published' => 0),
            array('id' => 178,'code' => 'QA','name' => "Qatar",'phonecode' => 974,'published' => 0),
            array('id' => 179,'code' => 'RE','name' => "Reunion",'phonecode' => 262,'published' => 0),
            array('id' => 180,'code' => 'RO','name' => "Romania",'phonecode' => 40,'published' => 0),
            array('id' => 181,'code' => 'RU','name' => "Russia",'phonecode' => 70,'published' => 0),
            array('id' => 182,'code' => 'RW','name' => "Rwanda",'phonecode' => 250,'published' => 1),
            array('id' => 183,'code' => 'SH','name' => "Saint Helena",'phonecode' => 290,'published' => 0),
            array('id' => 184,'code' => 'KN','name' => "Saint Kitts And Nevis",'phonecode' => 1869,'published' => 0),
            array('id' => 185,'code' => 'LC','name' => "Saint Lucia",'phonecode' => 1758,'published' => 0),
            array('id' => 186,'code' => 'PM','name' => "Saint Pierre and Miquelon",'phonecode' => 508,'published' => 0),
            array('id' => 187,'code' => 'VC','name' => "Saint Vincent And The Grenadines",'phonecode' => 1784,'published' => 0),
            array('id' => 188,'code' => 'WS','name' => "Samoa",'phonecode' => 684,'published' => 0),
            array('id' => 189,'code' => 'SM','name' => "San Marino",'phonecode' => 378,'published' => 0),
            array('id' => 190,'code' => 'ST','name' => "Sao Tome and Principe",'phonecode' => 239,'published' => 1),
            array('id' => 191,'code' => 'SA','name' => "Saudi Arabia",'phonecode' => 966,'published' => 0),
            array('id' => 192,'code' => 'SN','name' => "Senegal",'phonecode' => 221,'published' => 1),
            array('id' => 193,'code' => 'RS','name' => "Serbia",'phonecode' => 381,'published' => 0),
            array('id' => 194,'code' => 'SC','name' => "Seychelles",'phonecode' => 248,'published' => 1),
            array('id' => 195,'code' => 'SL','name' => "Sierra Leone",'phonecode' => 232,'published' => 1),
            array('id' => 196,'code' => 'SG','name' => "Singapore",'phonecode' => 65,'published' => 0),
            array('id' => 197,'code' => 'SK','name' => "Slovakia",'phonecode' => 421,'published' => 0),
            array('id' => 198,'code' => 'SI','name' => "Slovenia",'phonecode' => 386,'published' => 0),
            array('id' => 199,'code' => 'XG','name' => "Smaller Territories of the UK",'phonecode' => 44,'published' => 0),
            array('id' => 200,'code' => 'SB','name' => "Solomon Islands",'phonecode' => 677,'published' => 0),
            array('id' => 201,'code' => 'SO','name' => "Somalia",'phonecode' => 252,'published' => 1),
            array('id' => 202,'code' => 'ZA','name' => "South Africa",'phonecode' => 27,'published' => 0),
            array('id' => 203,'code' => 'GS','name' => "South Georgia",'phonecode' => 0,'published' => 0),
            array('id' => 204,'code' => 'SS','name' => "South Sudan",'phonecode' => 211,'published' => 1),
            array('id' => 205,'code' => 'ES','name' => "Spain",'phonecode' => 34,'published' => 0),
            array('id' => 206,'code' => 'LK','name' => "Sri Lanka",'phonecode' => 94,'published' => 0),
            array('id' => 207,'code' => 'SD','name' => "Sudan",'phonecode' => 249,'published' => 1),
            array('id' => 208,'code' => 'SR','name' => "Suriname",'phonecode' => 597,'published' => 0),
            array('id' => 209,'code' => 'SJ','name' => "Svalbard And Jan Mayen Islands",'phonecode' => 47,'published' => 0),
            array('id' => 210,'code' => 'SZ','name' => "Swaziland",'phonecode' => 268,'published' => 0),
            array('id' => 211,'code' => 'SE','name' => "Sweden",'phonecode' => 46,'published' => 0),
            array('id' => 212,'code' => 'CH','name' => "Switzerland",'phonecode' => 41,'published' => 0),
            array('id' => 213,'code' => 'SY','name' => "Syria",'phonecode' => 963,'published' => 0),
            array('id' => 214,'code' => 'TW','name' => "Taiwan",'phonecode' => 886,'published' => 0),
            array('id' => 215,'code' => 'TJ','name' => "Tajikistan",'phonecode' => 992,'published' => 0),
            array('id' => 216,'code' => 'TZ','name' => "Tanzania",'phonecode' => 255,'published' => 1),
            array('id' => 217,'code' => 'TH','name' => "Thailand",'phonecode' => 66,'published' => 0),
            array('id' => 218,'code' => 'TG','name' => "Togo",'phonecode' => 228,'published' => 1),
            array('id' => 219,'code' => 'TK','name' => "Tokelau",'phonecode' => 690,'published' => 0),
            array('id' => 220,'code' => 'TO','name' => "Tonga",'phonecode' => 676,'published' => 0),
            array('id' => 221,'code' => 'TT','name' => "Trinidad And Tobago",'phonecode' => 1868,'published' => 0),
            array('id' => 222,'code' => 'TN','name' => "Tunisia",'phonecode' => 216,'published' => 0),
            array('id' => 223,'code' => 'TR','name' => "Turkey",'phonecode' => 90,'published' => 0),
            array('id' => 224,'code' => 'TM','name' => "Turkmenistan",'phonecode' => 7370,'published' => 0),
            array('id' => 225,'code' => 'TC','name' => "Turks And Caicos Islands",'phonecode' => 1649,'published' => 0),
            array('id' => 226,'code' => 'TV','name' => "Tuvalu",'phonecode' => 688,'published' => 0),
            array('id' => 227,'code' => 'UG','name' => "Uganda",'phonecode' => 256,'published' => 1),
            array('id' => 228,'code' => 'UA','name' => "Ukraine",'phonecode' => 380,'published' => 0),
            array('id' => 229,'code' => 'AE','name' => "United Arab Emirates",'phonecode' => 971,'published' => 0),
            array('id' => 230,'code' => 'GB','name' => "United Kingdom",'phonecode' => 44,'published' => 0),
            array('id' => 231,'code' => 'US','name' => "United States",'phonecode' => 1,'published' => 0),
            array('id' => 232,'code' => 'UM','name' => "United States Minor Outlying Islands",'phonecode' => 1,'published' => 0),
            array('id' => 233,'code' => 'UY','name' => "Uruguay",'phonecode' => 598,'published' => 0),
            array('id' => 234,'code' => 'UZ','name' => "Uzbekistan",'phonecode' => 998,'published' => 0),
            array('id' => 235,'code' => 'VU','name' => "Vanuatu",'phonecode' => 678,'published' => 0),
            array('id' => 236,'code' => 'VA','name' => "Vatican City State (Holy See)",'phonecode' => 39,'published' => 0),
            array('id' => 237,'code' => 'VE','name' => "Venezuela",'phonecode' => 58,'published' => 0),
            array('id' => 238,'code' => 'VN','name' => "Vietnam",'phonecode' => 84,'published' => 0),
            array('id' => 239,'code' => 'VG','name' => "Virgin Islands (British)",'phonecode' => 1284,'published' => 0),
            array('id' => 240,'code' => 'VI','name' => "Virgin Islands (US)",'phonecode' => 1340,'published' => 0),
            array('id' => 241,'code' => 'WF','name' => "Wallis And Futuna Islands",'phonecode' => 681,'published' => 0),
            array('id' => 242,'code' => 'EH','name' => "Western Sahara",'phonecode' => 212,'published' => 0),
            array('id' => 243,'code' => 'YE','name' => "Yemen",'phonecode' => 967,'published' => 0),
            array('id' => 244,'code' => 'YU','name' => "Yugoslavia",'phonecode' => 38,'published' => 0),
            array('id' => 245,'code' => 'ZM','name' => "Zambia",'phonecode' => 260,'published' => 0),
            array('id' => 246,'code' => 'ZW','name' => "Zimbabwe",'phonecode' => 263,'published' => 0),
        );
        DB::table('pays')->insert($countries);
    }
}
