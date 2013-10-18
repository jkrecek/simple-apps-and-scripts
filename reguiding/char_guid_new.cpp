/*
GUID OVERFLOW FIXER
=============================================================
 - Changes biggest guids to lower which are free
 - look at line 36

 For compile use:
     g++ -o char_guid $(mysql_config --cflags) char_guid.cpp $(mysql_config --libs) -g -O2

*/

#include "char_guid_new.h"
char *buff_char = new char[99000];
void printLog(const char *fmt,...)
{
//  char *msg = new char[500];
  va_list argptr;
  va_start(argptr,fmt);
  vsprintf(buff_char,fmt,argptr);
  printf("%s", buff_char);
  fprintf(log_file, "%s", buff_char);
  fflush(stdout);
  va_end(argptr);
}

MYSQL *connectToDb(char* database)
{
    printLog("Connecting to database %s..\n", database);
    //char *socket_loc = NULL;
    //if(!server)
      //  socket_loc = socket;

    MYSQL *con = mysql_init(NULL);
    if (!mysql_real_connect(con, server, user, password, database, 0, NULL, 0))
    {
        printLog("%s\n", mysql_error(con));
        return NULL;
    }
    return con;
}

bool my_query(MYSQL *con, char *query,...)
{
  //printLog("%s\n:", query);
//    char *buff = new char[500];

    va_list argptr;
    va_start(argptr,query);
    vsprintf(buff_char,query,argptr);
    va_end(argptr);

    //    fprintf(log_file, "%s\n", buff_char);

    if (mysql_query(con, buff_char))
    {
        printLog("%s\n", mysql_error(con));
//        delete[] buff;
        return false;
    }
  //  delete[] buff;
    return true;
}

void incrementState()
{
    ++state;
    rewind(state_file);
    fwrite(&state, 1, 1, state_file);
    fflush(state_file);
}

void prepareStatement(char *table, MYSQL *pCon, MYSQL * newCon)
{
  MYSQL_RES *res = NULL;
  if (!my_query(pCon, "SELECT * FROM %s LIMIT 1;", table))
    return;
  
  res = mysql_use_result(pCon);
  uint8 col_count = mysql_num_fields(res);
  mysql_free_result(res);
  std::stringstream ss;
  ss << "PREPARE " << table << "_stm FROM \"INSERT INTO " << table << " VALUES ";
  for (uint8 ps = 0; ps < WORK_AT_ONCE;)
  {
    ss << "(";
    for(uint8 i = 0; i < col_count;)
    {
      ss << "?";
      ++i;
      if (i != col_count)
	ss << ",";
    }
    ss << ")";
    ++ps;
    if (ps != WORK_AT_ONCE)
      ss << ",";
  }
  ss << ";\";";
  //printLog("%s\n", (char*)ss.str().c_str());
  my_query(newCon, (char*)ss.str().c_str());
}

void updateTable(char* table, char* main_col, std::map<uint, uint>& guid, MYSQL *pCon, uint _total, MYSQL *pConOld)
{
  MYSQL_RES *res = NULL;
  MYSQL_ROW row = NULL;
  MYSQL_FIELD *fields;
  uint col_count;
  int main_col_pos = -1;
  for(uint cur_db_offset = 0; cur_db_offset <  _total; cur_db_offset += WORK_AT_ONCE)
  {
    if (!my_query(pConOld, "SELECT * FROM %s LIMIT %u,%u;", table, cur_db_offset, WORK_AT_ONCE))
      return;
    
    res = mysql_use_result(pConOld);
    col_count = mysql_num_fields(res);
    
    if (main_col_pos == -1)
    {
      fields = mysql_fetch_fields(res);
      for(uint _i = 0; _i < col_count; ++_i)
      {
	if ((const char*)main_col == std::string(fields[_i].name))
	{
	  main_col_pos = _i;
	  break;
	}
      }
    }
    
    //printLog("MAIN POS: %i\n", main_col_pos);
    
    uint row_count = 0;
    mysql_data_seek(res, 0);
    while((row = mysql_fetch_row(res)) != NULL)
    {
      for (int col = 0; col < col_count; ++col)
      {
	if (col == main_col_pos)
	{
	  //uint k =atoi(row[col]);
	  //printLog("old guid is %u new is %u\n", k, guid[k]);
	  my_query(pCon, "SET @%u_%i = '%u';", row_count, col, guid[atoi(row[col])]);
	}
	else
	{

	  std::stringstream val_name;
	  val_name << "@" << int(row_count) << "_" << int(col);
	  const char* cval = row[col];
	  if (cval && atoi(cval))
	    my_query(pCon, "SET %s = %i;", (char*)val_name.str().c_str(), atoi(cval));
	  else
	    my_query(pCon, "SET %s = '%s';", (char*)val_name.str().c_str(), cval);
	  //if (!my_query(pCon, "SET %s = '%s';", (char*)val_name.str().c_str()), row[col])
	  //{
	  //  printLog("GUID:%u VAL is /%s/ ... %s\n", atoi(row[0]), row[col], (char*)val_name.str().c_str());
	  //  exit(1);
	  //}
	}
      }
      ++row_count;
    }
    //printLog("count %u\n", row_count);
    mysql_free_result(res);
    
    my_query(pCon, "TRUNCATE TABLE %s", table);
    std::stringstream ss;
    ss << "EXECUTE " << table << "_stm USING ";
    for(int _r = 0; _r < WORK_AT_ONCE; )
    {
     for (int _c = 0; _c < col_count; )
     {
      ss << "@" << _r << "_" << _c;
      ++_c;
      if (_c != col_count)
	ss << ",";
      else
      {
	++_r;
	if (_r != WORK_AT_ONCE)
	  ss << ",";
	else
	  ss << ";";
      }
     }
    }
    //printLog("%s\n", (char*)ss.str().c_str());
    my_query(pCon, (char*)ss.str().c_str());
        
    printf("\rUpdating table '%s': %u %u%% complete ", table, cur_db_offset, uint(cur_db_offset*100/_total));
    fflush(stdout);
    
    //mysql_free_result(res);
  }
  printf("\n");
}

int main()
{
    std::stringstream file;
    file << "char_log_" << time(NULL);
    log_file = fopen(file.str().c_str(), "a");

    printLog("Char guid converter started, loading saved state file...\n");
    state_file = fopen("char_guid_state", "r+");
    if(!state_file)
    {
        printLog("State file does not exist, creating one..\n");
        state_file = fopen("char_guid_state", "w+");
        rewind(state_file);
        fwrite(&state, 1, 1, state_file);
        fflush(state_file);
    }
    else
    {
        fread(&state, 1, 1, state_file);
        printLog("State file found, state %u loaded\n", state);
    }

    conn_char = connectToDb(database_char);
    if(!conn_char)
    {
        fclose(log_file);
        fclose(state_file);
        return 0;
    }
    time_t startTime = time(0);
    time_t time_c = time(0);
    printLog("Connected\n");
    for(uint8 i = 0; i < REPEATING; ++i)
    {
        while(state < MAX_STEPS)
        {
            switch(state)
            {
                case 0: // characters
                {
                    printLog("        PROCESING CHARACTER GUIDs\n");
                    changeGuids("characters", "guid", tab_char, char_tables_count, OFFSET_CHARACTERS, conn_char);
                    time_t curTime = time(0);
                    printLog("Characters converted after %u seconds, total time is %u\n", curTime - time_c, curTime - startTime);
                    break;
                }
                case 1:
                {
                    printLog("        PROCESING GUILD GUIDs\n");
                    changeGuids("guild", "guildid", tab_guild, guild_tables_count, OFFSET_GUILD, conn_char);
                    time_t curTime = time(0);
                    printLog("Guilds converted after %u seconds, total time is %u\n", curTime - time_c, curTime - startTime);
                    break;
                }
                case 2:
                {
                    printLog("        PROCESING ITEM GUIDs\n");
                    changeGuids("item_instance", "guid", tab_item, item_tables_count, OFFSET_ITEMS, conn_char);
                    time_t curTime = time(0);
                    printLog("Items converted after %u seconds, total time is %u\n", curTime - time_c, curTime - startTime);
                    break;
                }
                case 3:
                {
                    printLog("        PROCESING PET GUIDs\n");
                    changeGuids("character_pet", "id", tab_pet, pet_tables_count, OFFSET_PETS, conn_char);
                    time_t curTime = time(0);
                    printLog("Pets converted after %u seconds, total time is %u\n", curTime - time_c, curTime - startTime);
                    break;
                }
                case 4:
                {
                    printLog("        PROCESING MAIL GUIDs\n");
                    changeGuids("mail", "id", tab_mail, mail_tables_count, OFFSET_MAIL, conn_char);
                    time_t curTime = time(0);
                    printLog("Mails converted after %u seconds, total time is %u\n", curTime - time_c, curTime - startTime);
                    break;
                }
                case 5:
                {
                    conn_acc = connectToDb(database_acc);
                    if(conn_acc)
                    {
                        printLog("        PROCESING ACCOUNT GUIDs\n");
                        changeGuids("account", "id", tab_acc, acc_tables_count, OFFSET_ACC, conn_acc, conn_char, tab_acc_char, acc_char_tables_count);
                        time_t curTime = time(0);
                        printLog("Accounts converted after %u seconds, total time is %u\n", curTime - time_c, curTime - startTime);
                        mysql_close(conn_acc);
                    }
                    else
                        printLog("Could not open connection to account database!");
                    break;
                }
            }
            time_c = time(0);
            incrementState();
        }
        printLog("Iteration %u of %u complete\n", i+1, REPEATING);
        state = 0;
        rewind(state_file);
        fwrite(&state, 1, 1, state_file);
        fflush(state_file);
    }
    fclose(log_file);
    fclose(state_file);
    mysql_close(conn_char);
    return 0;
}

bool changeGuids(char *main_table, char *main_col, char *tables[][2], uint tables_count, uint offset, MYSQL *con,
                 MYSQL *con_acc, char *tables_acc[][2], uint tables_count_acc)
{
    MYSQL_RES *res = NULL;
    MYSQL_ROW row;

    printLog("\nLOADING GUIDS FROM DB \n ");
    printLog("============================ \n");


    std::map<uint, uint> guidMap;

    if (!my_query(con, "SELECT %s FROM %s ORDER BY %s;", main_col, main_table, main_col))
        return false;

    res = mysql_use_result(con);


    printLog("\nPREPARING GUIDS\n ");
    printLog("============================ \n");

    uint guid_new = offset;

    while(row = mysql_fetch_row(res))
    {
	guidMap.insert(std::make_pair<uint, uint>(atoi(row[0]), uint(++guid_new)));
    }
    int guidCount = mysql_num_rows(res);
    
    std::map<uint, uint>::iterator itr = guidMap.begin() ;
    uint min_old = itr->first;
    uint min_new = itr->second;
    std::map<uint, uint>::reverse_iterator ritr = guidMap.rbegin() ;
    uint max_old = ritr->first;
    uint max_new = ritr->second;
    
    printLog("begin_old: %u, end_old: %u, begin_new: %u, end_new: %u, count: %u \n", 
	     min_old, max_old, min_new, max_new, guidCount);
    
    mysql_free_result(res);

    printLog("\nCREATING PREPARED STATEMENTS\n ");
    printLog("============================ \n");
    MYSQL * con_char_new = connectToDb(database_char_new);
    MYSQL * con_acc_new = connectToDb(database_acc_new);    
    {

	prepareStatement(main_table,con, con_char_new);
        for(uint8 y = 0; y < tables_count; ++y)
             prepareStatement(tables[y][0], con, con_char_new);
        if (con_acc)
	  for(uint8 y = 0; y < tables_count_acc; ++y)
	    prepareStatement(tables_acc[y][0], con_acc, con_acc_new);
    }
    
    printLog("\nUPDATING DATABASE\n ");
    printLog("============================ \n");
    

    updateTable(main_table, main_col, guidMap, con_char_new, guidCount, con);
    for(uint8 y = 0; y < tables_count; ++y)
      updateTable(tables[y][0], tables[y][1], guidMap, con_char_new, guidCount, con);
    
    if (con_acc)
      for(uint8 y = 0; y < tables_count_acc; ++y)
	updateTable(tables_acc[y][0], tables_acc[y][1], guidMap, con_acc_new, guidCount, con_acc);
      
    mysql_close(con_char_new);
    mysql_close(con_acc_new);
      
    printLog("\nREMOVING PREPARED STATEMENTS\n ");
    printLog("============================ \n");

    {
        my_query(con, "DEALLOCATE PREPARE %s_stm;", main_table);
	char* prev_table = NULL;
        for(uint8 y = 0; y < tables_count; ++y)
	{
	  if (prev_table == tables[y][0]);
	    continue;
	  
	  prev_table = tables[y][0];
          my_query(con, "DEALLOCATE PREPARE %s_stm;", tables[y][0]);
	}
        if(con_acc)
        {
            for(uint8 y = 0; y < tables_count_acc; ++y)
                my_query(con_acc, "DEALLOCATE PREPARE %s_stm;", tables_acc[y][0]);
        }
    }
    printf("\n");

    return true;
}

