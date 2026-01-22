<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'paygw_librapay', language 'ro'.
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['continuetopayment'] = 'Continuă către plată';
$string['duplicatetransaction'] = 'Această tranzacție a fost deja trimisă.';
$string['email'] = 'Email comerciant';
$string['email_help'] = 'Adresa de email pentru primirea notificărilor de plată de la LibraPay.';
$string['encryptionkey'] = 'Cheie de criptare';
$string['encryptionkey_help'] = 'Cheia de criptare hexadecimală de 32 de caractere furnizată de LibraPay pentru calcularea P_SIGN.';
$string['gatewaydescription'] = 'LibraPay este un furnizor autorizat de gateway de plăți de către Libra Internet Bank pentru procesarea tranzacțiilor cu cardul în România.';
$string['gatewayname'] = 'LibraPay';
$string['invalidencryptionkey'] = 'Cheia de criptare trebuie să fie exact 32 de caractere hexadecimale.';
$string['invalidmerchant'] = 'ID-ul comerciantului trebuie să fie exact 15 cifre.';
$string['invalidresponse'] = 'Răspuns invalid primit de la gateway-ul de plăți.';
$string['invalidsignature'] = 'Verificarea plății a eșuat. Semnătura răspunsului este invalidă.';
$string['invalidterminal'] = 'ID-ul terminalului trebuie să fie exact 8 cifre.';
$string['merchant'] = 'ID comerciant';
$string['merchant_help'] = 'Identificatorul comerciantului de 15 cifre furnizat de LibraPay (format: 0000000 + Terminal).';
$string['merchname'] = 'Nume comerciant';
$string['merchname_help'] = 'Numele comerciantului/afacerii dumneavoastră așa cum este înregistrat la LibraPay.';
$string['merchurl'] = 'URL comerciant';
$string['merchurl_help'] = 'URL-ul site-ului dumneavoastră așa cum este înregistrat la LibraPay.';
$string['messageprovider:payment_failed'] = 'Notificare plată eșuată';
$string['messageprovider:payment_successful'] = 'Notificare plată reușită';
$string['noscript'] = 'JavaScript este necesar pentru a continua. Vă rugăm să faceți clic pe butonul de mai jos pentru a continua către plată.';
$string['payment:failed:message'] = 'Plata dumneavoastră de {$a->amount} {$a->currency} pentru "{$a->description}" a eșuat. Vă rugăm să încercați din nou sau să contactați suportul.';
$string['payment:failed:subject'] = 'Plată eșuată';
$string['payment:successful:message'] = 'Plata dumneavoastră de {$a->amount} {$a->currency} pentru "{$a->description}" a fost efectuată cu succes. ID comandă: {$a->orderid}. Puteți accesa achiziția la {$a->url}';
$string['payment:successful:subject'] = 'Plată reușită - Chitanță';
$string['paymentfailed'] = 'Plata a eșuat. Vă rugăm să încercați din nou sau să contactați suportul.';
$string['paymentpending'] = 'Plata este în curs de procesare. Veți fi notificat odată ce este finalizată.';
$string['paymentsuccessful'] = 'Plata a fost efectuată cu succes. Vă mulțumim pentru achiziție!';
$string['pluginname'] = 'LibraPay';
$string['pluginname_desc'] = 'Plugin-ul LibraPay vă permite să primiți plăți prin LibraPay (Libra Internet Bank).';
$string['privacy:metadata:paygw_librapay_transactions'] = 'Stochează datele tranzacțiilor de plată LibraPay.';
$string['privacy:metadata:paygw_librapay_transactions:amount'] = 'Suma plății.';
$string['privacy:metadata:paygw_librapay_transactions:orderid'] = 'ID-ul unic al comenzii pentru tranzacție.';
$string['privacy:metadata:paygw_librapay_transactions:timecreated'] = 'Momentul când a fost creată tranzacția.';
$string['privacy:metadata:paygw_librapay_transactions:userid'] = 'ID-ul utilizatorului care a efectuat plata.';
$string['processingerror'] = 'A apărut o eroare de procesare. Vă rugăm să încercați din nou mai târziu.';
$string['redirecting'] = 'Redirecționare către plată...';
$string['redirectingtolibrapay'] = 'Sunteți redirecționat către LibraPay pentru a finaliza plata. Vă rugăm să așteptați...';
$string['sessionmismatch'] = 'Verificarea sesiunii a eșuat. Vă rugăm să încercați plata din nou.';
$string['terminal'] = 'ID terminal';
$string['terminal_help'] = 'Identificatorul terminalului de 8 cifre furnizat de LibraPay.';
$string['testmode'] = 'Mod test';
$string['testmode_help'] = 'Utilizați mediul de test LibraPay (sandbox) pentru testarea plăților. Dezactivați această opțiune pentru plăți live în producție.';
$string['transactionalreadyprocessed'] = 'Această tranzacție a fost deja procesată.';
$string['transactiondenied'] = 'Tranzacția a fost refuzată de bancă. Vă rugăm să verificați detaliile cardului sau să încercați o altă metodă de plată.';
