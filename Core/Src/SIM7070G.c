#include "SIM7070G.h"
#include "nmea.h"
#include <string.h>

extern UART_HandleTypeDef huart1;
extern UART_HandleTypeDef huart2;

extern char sentence[];
extern char decoy[];
//char decoy[GPS_MAX + 1] = "AT+CGNSINF\r\n+CGNSINF: 1,1,20241022215712.000,-34.617220,-58.382760,26.266,0.00,-5.4,0,,7.2,7.2,1.1,,4,,235.8,119.2\r\n\r\nOK\r\n";
extern GPS_DATA GPS;

//Command packs

char *INIT_PACK[] =
{
	/*"AT+CGDCONT=1,\"IP\",\"datos.personal.com\"\r\n", *///AT+CNCFG=1,1,"datos.personal.com","datos","datos",0
	"AT+CNCFG=1,1,\"datos.personal.com\",\"datos\",\"datos\",0\r\n",
	/*"AT+CGACT=1,1\r\n",*/
	"AT+CFUN=1,1\r\n"
};

char *NTP_PACK[] =
{
		"AT+CNACT=0,1\r\n"
		"AT+CNTP=\"3.ar.pool.ntp.org\"\r\n",
		"AT+CNTP\r\n",
		"AT+CNACT=0,0\r\n"
};

char *UPD_PACK_OPEN[] =
{
	"AT+SHCONF=\"URL\",\"http://186.13.143.82\"\r\n",
	"AT+SHCONF=\"BODYLEN\",1024\r\n",
	"AT+SHCONF=\"HEADERLEN\",350\r\n"//,
	//"AT+SHCONN\r\n"
};

char *GPS_INIT_PACK[] =
{
	"AT+CGNSPWR=1\r\n",
	"AT+CGNSMOD=1,0,0,1,0\r\n" //TODO: seleccionar fuente, actual = GALILEO
};

char *GPS_REQUEST[] =
{
	"AT+CGNSINF\r\n"
};

char *BATT_REQUEST[] =
{
	"AT+CBC\r\n"
};

void SIM7070G_COMMAND(char **pack, uint8_t len)
{
	int retry = 0;

	for(int i = 0; i < len; i++)
		{
			memset(sentence,0,strlen(sentence));
			//HAL_UART_Transmit(&huart2, "\nTRANSMIT\n\r", strlen("TRANSMIT\n\n\r"), 500);
			HAL_UART_Transmit(&huart1, pack[i], strlen((char*)pack[i]), 500);
			//HAL_UART_Transmit(&huart2, pack[i], strlen((char*)pack[i]), 500);

			//HAL_UART_Transmit(&huart2, "\nRECEIVE\n\r", strlen("RECEIVE\n\n\r"), 500);
			HAL_UART_Receive(&huart1, (char*)sentence, GPS_MAX + 1, 2500);
			//HAL_UART_Transmit(&huart2, (char*)sentence, strlen((char*)sentence), 500);

			if(retry < 3 && strstr(sentence, "ERROR") != NULL)
			{
				i--;
				retry++;
			}
		}
}

void SIM7070G_COMMAND_SINGLE(char *pack) //horrible hack
{
	memset(sentence,0,strlen(sentence));
	//HAL_UART_Transmit(&huart2, "\nTRANSMIT\n\r", strlen("TRANSMIT\n\n\r"), 500);
	HAL_UART_Transmit(&huart1, pack, strlen((char*)pack), 500);
	//HAL_UART_Transmit(&huart2, pack[i], strlen((char*)pack[i]), 500);
	//HAL_UART_Transmit(&huart2, "\nRECEIVE\n\r", strlen("RECEIVE\n\n\r"), 500);
	HAL_UART_Receive(&huart1, (char*)sentence, GPS_MAX + 1, 2500);
	//HAL_UART_Transmit(&huart2, (char*)sentence, strlen((char*)sentence), 500);
}

void SIM7070G_INIT() //This takes fucking forever to execute, but is absolutely necessary
{
	  //INIT SIM7070G
	  //Set B9 (PWRKEY) to HIGH ASAP, nothing matters before this point
	  HAL_GPIO_WritePin(GPIOB, GPIO_PIN_9, GPIO_PIN_SET);
	  HAL_Delay(500);
	  HAL_GPIO_TogglePin(GPIOB, GPIO_PIN_9);
	  HAL_Delay(20000); //Nuclear option: we pull it down for 20 seconds to ensure it resets every time
	  HAL_GPIO_TogglePin(GPIOB, GPIO_PIN_9);
	  HAL_UART_Receive(&huart1, (char*)sentence, GPS_MAX + 1, 2500); //We basically wait until bootup confirmation

	  SIM7070G_COMMAND(INIT_PACK, sizeof(INIT_PACK)/sizeof(INIT_PACK[0]));

	  HAL_Delay(7000);

	  SIM7070G_COMMAND(NTP_PACK, sizeof(NTP_PACK)/sizeof(NTP_PACK[0]));

	  SIM7070G_COMMAND(UPD_PACK_OPEN, sizeof(UPD_PACK_OPEN)/sizeof(UPD_PACK_OPEN[0]));

}

void SIM7070G_GPS_REQUEST()
{
	SIM7070G_COMMAND(GPS_INIT_PACK, sizeof(GPS_INIT_PACK)/sizeof(GPS_INIT_PACK[0]));
	SIM7070G_COMMAND(GPS_REQUEST, sizeof(GPS_REQUEST)/sizeof(GPS_REQUEST[0]));

	PARSER(sentence, &GPS);
	SIM7070G_COMMAND_SINGLE("AT+CGNSPWR=0\r\n");
}

void SIM7070G_BATT_REQUEST()
{
	SIM7070G_COMMAND(BATT_REQUEST, sizeof(BATT_REQUEST)/sizeof(BATT_REQUEST[0]));
	PARSE_BATT(sentence, &GPS);

}

void PARSE_BATT(char* src, GPS_DATA *GPRMC)
{
	int cont = 0;
	const char s[3] = ", ";
	char *token;

	token = strtok_new(src, s);

	while(token != NULL)
	{
		switch(cont)
		{
			case 2:
				sscanf(token, "%d", &GPRMC->batt);
				break;

			default:
				break;
		}

		cont++;
		token = strtok_new(NULL, s);
	}

}

void SIM7070G_UPDATE(char* URL) //No idea whether this works
{

	//SIM7070G_COMMAND(UPD_PACK_OPEN, sizeof(UPD_PACK_OPEN)/sizeof(UPD_PACK_OPEN[0]));

	HAL_Delay(1000);

	SIM7070G_COMMAND_SINGLE("AT+SHCONN\r\n");

	HAL_Delay(5000);

	SIM7070G_COMMAND_SINGLE(URL); //not sure if this will work, due to strlen

	SIM7070G_COMMAND_SINGLE("AT+SHDISC\r\n");
}
