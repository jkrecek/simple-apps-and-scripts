project(OnlineCompile)
set(ONLINE_COMPILE_VERSION 0.1)

# CMake policies
cmake_minimum_required(VERSION 2.8)

ADD_EXECUTABLE(bin test)

INSTALL(TARGETS bin RUNTIME DESTINATION .)