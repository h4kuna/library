<?php

namespace Utility;

class Feast extends NonObject
{
	const CZECH_DATE_ZERO = 'd.m.Y';
	const CZECH_DATE_ZERO_SHORT = 'd.m.y';

	/**
	 * vrati nazev dne v cestine
	 * @return string
	 */
	static public function nameOfDay($day = NULL)
	{
		if ($day === NULL) {
			$day = (int) date('w');
		}

		static $days = array('Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota');

		if (isset($days[$day])) {
			return $days[$day];
		}

		if ($day < 0) {
			return 0;
		}

		throw new \Exception('Invalid number for day, interval is 0-6, 0 = Sunday');
	}

	static public function nameOfMonth($month = NULL)
	{
		if ($day === NULL) {
			$day = (int) date('n');
		}

		static $days = array(1 => 'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen',
				'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec');

		if (isset($days[$day])) {
			return $days[$day];
		}

		if ($day < 1) {
			return 0;
		}

		throw new \Exception('Invalid number for day, interval is 1-12.');
	}

	/**
	 *
	 * @param $date string CZECH FORMAT
	 * @return date
	 */
	static public function czechDate2Sql($date='01.01.1991')
	{
		if (empty($date))
			return NULL;
		$date = explode('.', $date);
		return sprintf("%04d-%02d-%02d", $date[2], $date[1], $date[0]);
	}

	/**
	 * na zaklade data vraci pocet let
	 * @param int $day
	 * @param int $month
	 * @param int $year
	 * @param bool $int TRUE - zobrazi jako int jinak jako float
	 * @return number
	 */
	static public function age($day, $month, $year, $int=TRUE)
	{
		$age = (time() - mktime(0, 0, 0, $month, $day, $year) ) / 31557600;
		return ($int) ? (int) $age : $age;
	}

	/**
	 * vrati pocet dni v unoru
	 * @param int $year -rok pro ktrery chci zjistit
	 * @return int
	 */
	static public function februaryOfDay($year)
	{
		return checkdate(2, 29, $year) ? 29 : 28;
	}

	/**
	 * podle zadaneho data vraci kdo ma svatek
	 * @param string|DateTime|int $day
	 * @param int $month
	 * @return string
	 */
	static public function getName($day=FALSE, $month=FALSE)
	{
		if (\is_string($day))
			$day = new \DateTime($day);

		if ($day === FALSE && $month === FALSE) {
			$day = date('d');
			$month = date('m');
		} elseif ($day instanceof \DateTime) {
			$month = $day->format('m');
			$day = $day->format('d');
		} elseif ($day === FALSE || $month === FALSE)
			throw new \Exception('You must fill both param as int or not one.');

		$FALSE = 0;

		switch ($month) {
			case 1:
				switch ($day) {
					case 1: return 'Nový rok';
					case 2: return 'Karina';
					case 3: return 'Radmila';
					case 4: return 'Diana';
					case 5: return 'Dalimil';
					case 6: return 'Tři králové';
					case 7: return 'Vilma';
					case 8: return 'Čestmír';
					case 9: return 'Vladan';
					case 10: return 'Břetislav';
					case 11: return 'Bohdana';
					case 12: return 'Pravoslav';
					case 13: return 'Edita';
					case 14: return 'Radovan';
					case 15: return 'Alice';
					case 16: return 'Ctirad';
					case 17: return 'Drahoslav';
					case 18: return 'Vladislav';
					case 19: return 'Doubravka';
					case 20: return 'Ilona';
					case 21: return 'Běla';
					case 22: return 'Slavomír';
					case 23: return 'Zdeněk';
					case 24: return 'Milena';
					case 25: return 'Miloš';
					case 26: return 'Zora';
					case 27: return 'Ingrid';
					case 28: return 'Otýlie';
					case 29: return 'Zdislava';
					case 30: return 'Robin';
					case 31: return 'Marika';
					default: $FALSE = 31;
						break;
				};
				break;
			case 2:
				switch ($day) {
					case 1: return 'Hynek';
					case 2: return 'Nela';
					case 3: return 'Blažej';
					case 4: return 'Jarmila';
					case 5: return 'Dobromila';
					case 6: return 'Vanda';
					case 7: return 'Veronika';
					case 8: return 'Milada';
					case 9: return 'Apolena';
					case 10: return 'Mojmír';
					case 11: return 'Božena';
					case 12: return 'Slavěna';
					case 13: return 'Věnceslav';
					case 14: return 'Valentýn';
					case 15: return 'Jiřina';
					case 16: return 'Ljuba';
					case 17: return 'Miloslava';
					case 18: return 'Gizela';
					case 19: return 'Patrik';
					case 20: return 'Oldřich';
					case 21: return 'Lenka';
					case 22: return 'Petr';
					case 23: return 'Svatopluk';
					case 24: return 'Matěj';
					case 25: return 'Liliana';
					case 26: return 'Dorota';
					case 27: return 'Alexandr';
					case 28: return 'Lumír';
					case 29: return '';
					default: $FALSE = 29;
						break;
				};
				break;
			case 3:
				switch ($day) {
					case 1: return 'Bedřich';
					case 2: return 'Anežka';
					case 3: return 'Kamil';
					case 4: return 'Stela';
					case 5: return 'Kazimir';
					case 6: return 'Miroslav';
					case 7: return 'Tomáš';
					case 8: return 'Gabriela';
					case 9: return 'Františka';
					case 10: return 'Viktorie';
					case 11: return 'Anděla';
					case 12: return 'Řehoř';
					case 13: return 'Růžena';
					case 14: return 'Růt a matylda';
					case 15: return 'Ida';
					case 16: return 'Elena a herbert';
					case 17: return 'Vlastimil';
					case 18: return 'Eduard';
					case 19: return 'Josef';
					case 20: return 'Světlana';
					case 21: return 'Radek';
					case 22: return 'Leona';
					case 23: return 'Ivona';
					case 24: return 'Gabriel';
					case 25: return 'Marian';
					case 26: return 'Emanuel';
					case 27: return 'Dita';
					case 28: return 'Soňa';
					case 29: return 'Taťána';
					case 30: return 'Arnošt';
					case 31: return 'Kvido';
					default: $FALSE = 31;
						break;
				};
				break;
			case 4:
				switch ($day) {
					case 1: return 'Hugo';
					case 2: return 'Erika';
					case 3: return 'Richard';
					case 4: return 'Ivana';
					case 5: return 'Miroslava';
					case 6: return 'Vendula';
					case 7: return 'Heřman a Hermína';
					case 8: return 'Ema';
					case 9: return 'Dušan';
					case 10: return 'Darja';
					case 11: return 'Izabela';
					case 12: return 'Julius';
					case 13: return 'Aleš';
					case 14: return 'Vincenc';
					case 15: return 'Anastázie';
					case 16: return 'Irena';
					case 17: return 'Rudolf';
					case 18: return 'Valérie';
					case 19: return 'Rostislav';
					case 20: return 'Marcela';
					case 21: return 'Alexandra';
					case 22: return 'Evženie';
					case 23: return 'Vojtěch';
					case 24: return 'Jiří';
					case 25: return 'Marek';
					case 26: return 'Oto';
					case 27: return 'Jaroslav';
					case 28: return 'Vlastislav';
					case 29: return 'Robert';
					case 30: return 'Blahoslav';
					default: $FALSE = 30;
						break;
				};
				break;
			case 5:
				switch ($day) {
					case 1: return 'Svátek práce';
					case 2: return 'Zikmund';
					case 3: return 'Alexej';
					case 4: return 'Květoslav';
					case 5: return 'Klaudie';
					case 6: return 'Radoslav';
					case 7: return 'Stanislav';
					case 8: return 'Den osvobození ČSR - 1945';
					case 9: return 'Ctibor';
					case 10: return 'Blažena';
					case 11: return 'Svatava';
					case 12: return 'Pankrác';
					case 13: return 'Servác';
					case 14: return 'Bonifác';
					case 15: return 'Žofie';
					case 16: return 'Přemysl';
					case 17: return 'Aneta';
					case 18: return 'Nataša';
					case 19: return 'Ivo';
					case 20: return 'Zbyšek';
					case 21: return 'Monika';
					case 22: return 'Emil';
					case 23: return 'Vladimír';
					case 24: return 'Jana';
					case 25: return 'Viola';
					case 26: return 'Filip';
					case 27: return 'Valdemar';
					case 28: return 'Vilém';
					case 29: return 'Maxmilián';
					case 30: return 'Ferdinand';
					case 31: return 'Kamila';
					default: $FALSE = 31;
						break;
				};
				break;
			case 6:
				switch ($day) {
					case 1: return 'Laura';
					case 2: return 'Jarmil';
					case 3: return 'Tamara';
					case 4: return 'Dalibor';
					case 5: return 'Dobroslav';
					case 6: return 'Norbert';
					case 7: return 'Iveta a Slavoj';
					case 8: return 'Medard';
					case 9: return 'Stanislava';
					case 10: return 'Gita';
					case 11: return 'Bruno';
					case 12: return 'Antonie';
					case 13: return 'Antonín';
					case 14: return 'Roland';
					case 15: return 'Vít';
					case 16: return 'Zbyněk';
					case 17: return 'Adolf';
					case 18: return 'Milan';
					case 19: return 'Leoš';
					case 20: return 'Květa';
					case 21: return 'Alois';
					case 22: return 'Pavla';
					case 23: return 'Zdeňka';
					case 24: return 'Jan';
					case 25: return 'Ivan';
					case 26: return 'Adriana';
					case 27: return 'Ladislav';
					case 28: return 'Lubomír';
					case 29: return 'Petr a Pavel';
					case 30: return 'Šárka';
					default: $FALSE = 30;
						break;
				}
				break;
			case 7:
				switch ($day) {
					case 1: return 'Jaroslava';
					case 2: return 'Patricie';
					case 3: return 'Radomír';
					case 4: return 'Prokop';
					case 5: return 'Dem slovanských věrozvěstů Cyrila a Metoděje';
					case 6: return 'Upálení mistra Jana Husa - 1415';
					case 7: return 'Bohuslava';
					case 8: return 'Nora';
					case 9: return 'Drahoslava';
					case 10: return 'Libuše a Amálie';
					case 11: return 'Olga';
					case 12: return 'Bořek';
					case 13: return 'Markéta';
					case 14: return 'Karolína';
					case 15: return 'Jindřich';
					case 16: return 'Luboš';
					case 17: return 'Martina';
					case 18: return 'Drahomíra';
					case 19: return 'Čeněk';
					case 20: return 'Ilja';
					case 21: return 'Vítězslav';
					case 22: return 'Magdaléna';
					case 23: return 'Libor';
					case 24: return 'Kristýna';
					case 25: return 'Jakub';
					case 26: return 'Anna';
					case 27: return 'Věroslav';
					case 28: return 'Viktor';
					case 29: return 'Marta';
					case 30: return 'Bořivoj';
					case 31: return 'Ignác';
					default: $FALSE = 31;
						break;
				};
				break;
			case 8:
				switch ($day) {
					case 1: return 'Oskar';
					case 2: return 'Gustav';
					case 3: return 'Miluše';
					case 4: return 'Dominik';
					case 5: return 'Kristian';
					case 6: return 'Oldřiška';
					case 7: return 'Lada';
					case 8: return 'Soběslav';
					case 9: return 'Roman';
					case 10: return 'Vavřinec';
					case 11: return 'Zuzana';
					case 12: return 'Klára';
					case 13: return 'Alena';
					case 14: return 'Alan';
					case 15: return 'Hana';
					case 16: return 'Jáchym';
					case 17: return 'Petra';
					case 18: return 'Helena';
					case 19: return 'Ludvík';
					case 20: return 'Bernard';
					case 21: return 'Johana';
					case 22: return 'Bohuslav';
					case 23: return 'Sandra';
					case 24: return 'Bartoloměj';
					case 25: return 'Radim';
					case 26: return 'Luděk';
					case 27: return 'Otakar';
					case 28: return 'Augustýn';
					case 29: return 'Evelína';
					case 30: return 'Vladěna';
					case 31: return 'Pavlína';
					default: $FALSE = 31;
						break;
				};
				break;
			case 9:
				switch ($day) {
					case 1: return 'Linda a Samuel';
					case 2: return 'Adéla';
					case 3: return 'Bronislav';
					case 4: return 'Jindřiška';
					case 5: return 'Boris';
					case 6: return 'Boleslav';
					case 7: return 'Regína';
					case 8: return 'Mariana';
					case 9: return 'Daniela';
					case 10: return 'Irma';
					case 11: return 'Denisa';
					case 12: return 'Marie';
					case 13: return 'Lubor';
					case 14: return 'Radka';
					case 15: return 'Jolana';
					case 16: return 'Ludmila';
					case 17: return 'Naděžda';
					case 18: return 'Kryštof';
					case 19: return 'Zita';
					case 20: return 'Oleg';
					case 21: return 'Matouš';
					case 22: return 'Darina';
					case 23: return 'Berta';
					case 24: return 'Jaromír';
					case 25: return 'Zlata';
					case 26: return 'Andrea';
					case 27: return 'Jonáš';
					case 28: return 'Václav';
					case 29: return 'Michal';
					case 30: return 'Jeroným';
					default: $FALSE = 30;
						break;
				};
				break;
			case 10:
				switch ($day) {
					case 1: return 'Igor';
					case 2: return 'Olivie a Oliver';
					case 3: return 'Bohumil';
					case 4: return 'František';
					case 5: return 'Eliška';
					case 6: return 'Hanuš';
					case 7: return 'Justýna';
					case 8: return 'Věra';
					case 9: return 'Štefan a Sára';
					case 10: return 'Marina';
					case 11: return 'Andrej';
					case 12: return 'Marcel';
					case 13: return 'Renáta';
					case 14: return 'Agáta';
					case 15: return 'Tereza';
					case 16: return 'Havel';
					case 17: return 'Hedvika';
					case 18: return 'Lukáš';
					case 19: return 'Michaela';
					case 20: return 'Vendelín';
					case 21: return 'Brigita';
					case 22: return 'Sabina';
					case 23: return 'Teodor';
					case 24: return 'Nina';
					case 25: return 'Beáta';
					case 26: return 'Erik';
					case 27: return 'Šarlota a Zoe';
					case 28: return 'Založení ČSR - 1918';
					case 29: return 'Silvie';
					case 30: return 'Tadeáš';
					case 31: return 'Štěpánka';
					default: $FALSE = 31;
						break;
				};
				break;
			case 11:
				switch ($day) {
					case 1: return 'Felix';
					case 2: return 'Památka zesnulých';
					case 3: return 'Hubert';
					case 4: return 'Karel';
					case 5: return 'Miriam';
					case 6: return 'Liběna';
					case 7: return 'Saskie';
					case 8: return 'Bohumír';
					case 9: return 'Bohdan';
					case 10: return 'Evžen';
					case 11: return 'Martin';
					case 12: return 'Benedikt';
					case 13: return 'Tibor';
					case 14: return 'Sáva';
					case 15: return 'Leopold';
					case 16: return 'Otmar';
					case 17: return 'Mahulena';
					case 18: return 'Romana';
					case 19: return 'Alžběta';
					case 20: return 'Nikola';
					case 21: return 'Albert';
					case 22: return 'Cecílie';
					case 23: return 'Klement';
					case 24: return 'Emílie';
					case 25: return 'Kateřina';
					case 26: return 'Artur';
					case 27: return 'Xenie';
					case 28: return 'René';
					case 29: return 'Zina';
					case 30: return 'Ondřej';
					case 31: return 'Iva';
					default: $FALSE = 31;
						break;
				};
				break;
			case 12:
				switch ($day) {
					case 1: return 'Iva';
					case 2: return 'Blanka';
					case 3: return 'Svatoslav';
					case 4: return 'Barbora';
					case 5: return 'Jitka';
					case 6: return 'Mikuláš';
					case 7: return 'Ambrož a Benjamín';
					case 8: return 'Květoslava';
					case 9: return 'Vratislav';
					case 10: return 'Julie';
					case 11: return 'Dana';
					case 12: return 'Simona';
					case 13: return 'Lucie';
					case 14: return 'Lýdie';
					case 15: return 'Radana a Radan';
					case 16: return 'Albína';
					case 17: return 'Daniel';
					case 18: return 'Miloslav';
					case 19: return 'Ester';
					case 20: return 'Dagmar';
					case 21: return 'Natálie';
					case 22: return 'Šimon';
					case 23: return 'Vlasta';
					case 24: return 'Adam a Eva';
					case 25: return 'Boží hod vánoční';
					case 26: return 'Štěpán';
					case 27: return 'Žaneta';
					case 28: return 'Bohumila';
					case 29: return 'Judita';
					case 30: return 'David';
					case 31: return 'Silvestr';
					default: $FALSE = 31;
						break;
				};
				break;
			default:
				throw new \Exception('Month is out of range, $month = ' . $month);
				break;
		}
		if ($FALSE > 0)
			throw new \Exception('$day is out of range, $day = ' . $day);
	}

}
