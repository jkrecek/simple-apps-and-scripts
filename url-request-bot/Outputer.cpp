#include "Outputer.h"
#include <cstdio>

#define BAR_SIZE 20
const char* endline = "\n\r";
Outputer::Outputer()
{
    time_m = new QTime();
    current = 0;
}

void Outputer::PrintLine(QString line)
{
    printf(QString(line+endline).toStdString().c_str());
}

void Outputer::Refresh()
{
    printf((QString("\r")+"Current connection count is: "+QString::number(current)).toStdString().c_str());
}
