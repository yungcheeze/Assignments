import cv2
# import os
import re
import math
import numpy as np
from scipy.ndimage import label
# import threading
# from queue import Queue
from os import listdir
from os.path import isfile, join


def sytox_enhancement(sytox_in):
    """
    input: original 16bit sytox (w1) image
    output: visually enhanced image
    """
    clahe = cv2.createCLAHE(clipLimit=200.0, tileGridSize=(20, 20))
    sytox_out = clahe.apply(sytox_in)
    sytox_out = cv2.medianBlur(sytox_out, 3)
    uniform_darkening(sytox_out)
    return sytox_out


def brightfield_enhancement(brightfield_in):
    """
    input: original 16bit sytox (w1) image
    output: visually enhanced image
    """
    brightfield_out = brightfield_in.copy()
    powerlaw_transform(brightfield_out, 1.35)
    clahe = cv2.createCLAHE(clipLimit=4.0, tileGridSize=(8, 8))
    brightfield_out = clahe.apply(brightfield_out)
    return brightfield_out


def LoG(i_8u):
    """
    input: 8-bit image
    output: image with laplacian of Gaussian filter applied
    """
    canny = cv2.Canny(i_8u, 90, 190)
    log = cv2.addWeighted(i_8u, 1, canny, -0.3, 0)
    return log


def unsharp(i_8u):
    """
    input: 8-bit image
    output: image with unsharp filter applied
    """
    blur = cv2.GaussianBlur(i_8u, (17, 17), 5)
    edges = cv2.subtract(i_8u, blur)
    unsharp = cv2.addWeighted(i_8u, 1, edges, 0.8, 0)
    return unsharp


def create_sytox_markers(img):
    """
    input: sytox binary image
    output: dilated skeleton of image (to be used in watershedding)
    """
    close = cv2.erode(img, None, iterations=5)
    skel = skeletonise(close)
    skel = cv2.dilate(skel, None, iterations=3)
    cv2.GaussianBlur(skel, (11, 11), 2)
    return skel


def create_sytox_borders(triple_res):
    """
    input: sytox binary image
    output: border image (to be used in watershedding)
    """
    t_inv = cv2.bitwise_not(triple_res)
    dt = cv2.distanceTransform(t_inv, cv2.DIST_L2, 5)
    cv2.normalize(dt, dt, 0.0, 1.0, cv2.NORM_MINMAX)
    ret, dt = cv2.threshold(dt, 0.05*dt.max(), 255, 0)
    dt_inv = dt.copy()
    dt_inv = np.uint8(dt_inv)
    cv2.bitwise_not(dt_inv, dt_inv)
    border = cv2.erode(dt_inv, None, iterations=6)
    border = border - cv2.erode(border, None)
    return border


def create_brightfield_markers(b_thresh):
    """
    input: brigthfield binary image
    output: dilated skeleton of image (to be used in watershedding)
    """
    opening = cv2.erode(b_thresh, None, iterations=5)
    b_skel = skeletonise(opening)
    b_skel = cv2.dilate(b_skel, None, iterations=3)
    cv2.GaussianBlur(b_skel, (11, 11), 2)
    return b_skel


def create_brightfield_borders(b_thresh):
    """
    input: sytox binary image
    output: border image (to be used in watershedding)
    """
    t = cv2.bitwise_not(b_thresh)
    dt = cv2.distanceTransform(t,cv2.DIST_L2,5)
    cv2.normalize(dt, dt, 0.0, 1.0, cv2.NORM_MINMAX);
    ret, dt = cv2.threshold(dt,0.05*dt.max(),255,0)
    dt_inv = dt.copy()
    dt_inv = np.uint8(dt_inv)
    cv2.bitwise_not(dt_inv, dt_inv)
    border = cv2.erode(dt_inv, None, iterations=6)
    border = border - cv2.erode(border, None, iterations=3)
    return border


def watershed(markers, borders, bgr_img):
    """
    input: markers, borders, bgr_image
    output: - grayscale watershed,
            - inverse of grayscale watershed,
            - number of connected components
    reference:http://stackoverflow.com/questions/11294859/how-to-define-the-markers-for-watershed-in-opencv
    """
    lbl, ncc = label(markers)
    lbl = lbl * (255/ncc)
    lbl[borders == 255] = 255
    lbl = lbl.astype(np.int32)
    cv2.watershed(bgr_img, lbl)
    lbl[lbl == -1] = 0
    lbl = lbl.astype(np.uint8)
    lbl_inv = 255 - lbl
    return lbl, lbl_inv, ncc


def watershed_light(markers, borders, bgr_img):
    """
    input: markers, borders, bgr_image
    output: grayscale watershed with low brighness variation (easier to threshold)
    """
    lbl, ncc = label(markers)
    lbl = lbl * (100/ncc)
    lbl[borders == 255] = 255
    lbl = lbl.astype(np.int32)
    cv2.watershed(bgr_img, lbl)
    lbl[lbl == -1] = 0
    lbl = lbl.astype(np.uint8)
    lbl_inv = 255 - lbl
    return lbl_inv


def adjust_brightfield_threshold(b_thresh):
    """
    input: initial brightfield threshold binary
    output: same image binary with white outer border removed
    """
    kernel = np.ones((2, 2), np.uint8)
    triple_res = cv2.resize(b_thresh, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
    b_inv = cv2.bitwise_not(triple_res)
    cv2.dilate(b_inv, kernel, b_inv, iterations=1)
    blur = cv2.GaussianBlur(b_inv, (13, 13),5)
    blur = b_inv.copy()
    h, w = triple_res.shape
    mask = cv2.resize(blur, (w+2, h+2), interpolation=cv2.INTER_CUBIC)
    t = triple_res.copy()
    retv, image, val, rect = cv2.floodFill(t, mask, (0, 0), 0)
    return image

def powerlaw_transform(I, gamma):
    """
    perform gamma correction on image
    input:image, gamma value
    output: same image with gamma enhancement applied (note: function will edit
    provided image. if you would like to retain original pass a copied image
    i.e. using image.copy())
    reference: taken from gamma.py  in practicals
    """
    for i in range(I.shape[0]):  # image width
        for j in range(I.shape[1]):  # image height
                I[i, j] = int(math.pow(I[i, j], gamma))


def skeletonise(img):
    """
    input: 8-bit binary image
    output: skeletonised version of input image
    """
    # http://opencvpython.blogspot.co.uk/2012/05/skeletonization-using-opencv-python.html
    size = np.size(img)
    skel = np.zeros(img.shape,np.uint8)

    ret,img = cv2.threshold(img,127,255,0)
    element = cv2.getStructuringElement(cv2.MORPH_CROSS,(3,3))
    done = False

    while( not done):
        eroded = cv2.erode(img,element)
        temp = cv2.dilate(eroded,element)
        temp = cv2.subtract(img,temp)
        skel = cv2.bitwise_or(skel,temp)
        img = eroded.copy()

        zeros = size - cv2.countNonZero(img)
        if zeros==size:
            done = True

    return skel


def uniform_darkening(image, thresh=12000, floor=6000):
    """
    input: 16bit sytox image, threshold value(optional), lower bound(i,e darkest
    image should be; optional)
    """
    for i in range(image.shape[0]):  # image width
        for j in range(image.shape[1]):  # image height
            if image[i, j] < thresh:
                image[i, j] = floor


class WindowManager(object):
    """
    wrapper for opencv windows
    create:i.e  wm = WindowManager()
    add window: wm.add(image, "windowname")
    display windows: wm.show
    """
    def __init__(self):
        self.WINDOWS = dict()

    def add(self, image, window_name):
        if image is not None:
            self.WINDOWS[window_name] = image
        else:
            print("Invalid image provided for window '{}'".format(window_name))

    def show(self):
        for window_name, image in self.WINDOWS.items():
            cv2.namedWindow(window_name, cv2.WINDOW_NORMAL)
            cv2.resizeWindow(window_name, 696, 520)
            cv2.imshow(window_name, image)
        key = cv2.waitKey(0) & 0xFF  # wait (i.e.1000ms/25fps = 40ms)
        if (key == ord('x')):
            cv2.destroyAllWindows()


def get_image_name(folder, row, column, w_n):
    """
    appends requested image name to "folder".
    the returned string can then be used in cv2.imread functions
    params : - row = wellrow character "A" to "E"
             - col = well column int 1 to 23
             - w_n = image channel 1 or 2
    """
    image_names = get_image_names(folder)
    img_name = ""
    column = str(column).zfill(2)
    s_re = re.compile(r"{}{}_w{}".format(row, column, w_n))
    for img in image_names:
        if re.search(s_re, img) is not None:
            img_name = img
            break
    if img_name == "":
        return None
    folder += img_name
    return folder


def get_binary_image_name(folder, row, column):
    """
    returns image corresponding to params
    params : - row = wellrow character "A" to "E"
             - col = well column int 1 to 23
             - w_n = image channel 1 or 2
    """
    image_names = get_image_names(folder)
    img_name = ""
    column = str(column).zfill(2)
    s_re = re.compile(r"{}{}".format(row, column))
    for img in image_names:
        if re.search(s_re, img) is not None:
            img_name = img
            break
    if img_name == "":
        return None
    folder += img_name
    return folder


def get_image_names(folder):
    path = folder
    onlyfiles = [f for f in listdir(path) if isfile(join(path, f))]
    return onlyfiles
